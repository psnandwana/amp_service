<?php
namespace App\Controller;

use Cake\ORM\TableRegistry;
use RestApi\Controller\ApiController;

class RmController extends ApiController
{
    public function requestcount()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $rm_id = $this->request->getData('rm_id');
            $AmpGrievance = TableRegistry::get('Grievance', ['table' => 'amp_grievance']);
            $options = array();
            $options['conditions']['rm_id'] = $rm_id;
            $totalRequests = $AmpGrievance->find('all', $options)->count();
            // pending request count
            $options['conditions']['rm_approval_status'] = "0";
            $pendingRequests = $AmpGrievance->find('all', $options)->count();
            // approved request count
            $options['conditions']['status'] = "Resolved";
            $approvedRequests = $AmpGrievance->find('all', $options)->count();
            // rejected request count
            $options['conditions']['status'] = "Rejected";
            $rejectedRequests = $AmpGrievance->find('all', $options)->count();
            $this->httpStatusCode = 200;
            $this->apiResponse['all'] = $totalRequests;
            $this->apiResponse['fulfilled'] = $approvedRequests;
            $this->apiResponse['pending'] = $pendingRequests;
            $this->apiResponse['rejected'] = $rejectedRequests;
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function getallrequests()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $AmpGrievance = TableRegistry::get('Grievance', ['table' => 'amp_grievance']);
            $rm_id = $this->request->getData('rm_id');
            $page = $this->request->getData('page');
            $limit = 10;
            $start = ($page - 1) * $limit;
            $options = array();
            $options['conditions']['rm_id'] = $rm_id;

            $options['fields'] = array(
                'id' => 'Grievance.id',
                'employee_id' => 'Employee.id',
                'employee_name' => 'Employee.emp_name',
                'rm_name' => 'Admin.name',
                'request_type',
                'subject',
                'description',
                'submitted_date' => 'Grievance.submitted_date',
                'status' => 'Grievance.status');

            $options['join'] = array(
                array(
                    'table' => 'amp_employees_listing',
                    'alias' => 'Employee',
                    'type' => 'INNER',
                    'conditions' => 'Grievance.employee_id = Employee.id',
                ),
                array(
                    'table' => 'amp_admin_user',
                    'alias' => 'Admin',
                    'type' => 'INNER',
                    'conditions' => 'Admin.email = Employee.rm_email_id',
                ),
            );
            $options['limit'] = $limit;
            $options['order'] = 'Grievance.submitted_date DESC';
            $options['offset'] = $start;
            $allRequests = $AmpGrievance->find('all', $options)->toArray();
            foreach ($allRequests as $index => $request) {
                $allRequests[$index]['submitted_date'] = date("jS F, Y", strtotime($request['submitted_date']));
            }

            $this->httpStatusCode = 200;
            $this->apiResponse['data'] = $allRequests;
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function getpendingrequests()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $rm_id = 0;
            $page = $this->request->getData('page');
            $limit = 10;
            $start = ($page - 1) * $limit;
            $options = array();
            if ($this->request->getData('rm_id') != "") {
                $rm_id = $this->request->getData('rm_id');
            }
            $options['conditions']['rm_id'] = $rm_id;
            $options['conditions']['rm_approval_status'] = '0';
            $options['limit'] = $limit;
            $options['order'] = 'submitted_date DESC';
            $options['offset'] = $start;
            $requestList = $this->AmpGrievance->find('all', $options)->toArray();
            $this->httpStatusCode = 200;
            $this->apiResponse['requests'] = $requestList;
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function getapprovedrequests()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $rm_id = 0;
            $page = $this->request->getData('page');
            $limit = 10;
            $start = ($page - 1) * $limit;
            $options = array();
            if ($this->request->getData('rm_id') != "") {
                $rm_id = $this->request->getData('rm_id');
            }
            $options['conditions']['rm_id'] = $rm_id;
            $options['conditions']['status'] = 'Resolved';
            $options['limit'] = $limit;
            $options['order'] = 'submitted_date DESC';
            $options['offset'] = $start;
            $requestList = $this->AmpGrievance->find('all', $options)->toArray();
            $this->httpStatusCode = 200;
            $this->apiResponse['requests'] = $requestList;
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function getrejectedrequests()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $rm_id = 0;
            $page = $this->request->getData('page');
            $limit = 10;
            $start = ($page - 1) * $limit;
            $options = array();
            if ($this->request->getData('rm_id') != "") {
                $rm_id = $this->request->getData('rm_id');
            }
            $options['conditions']['rm_id'] = $rm_id;
            $options['conditions']['status'] = 'Rejected';
            $options['limit'] = $limit;
            $options['order'] = 'submitted_date DESC';
            $options['offset'] = $start;
            $requestList = $this->AmpGrievance->find('all', $options)->toArray();
            $this->httpStatusCode = 200;
            $this->apiResponse['requests'] = $requestList;
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function updaterequeststatus()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $AmpGrievance = TableRegistry::get('Grievance', ['table' => 'amp_grievance']);
            $req_id = $this->request->getData('request_id');
            $status = $this->request->getData('status');
            $remark = $this->request->getData('remark');
            $options = array();
            $options['conditions']['id'] = $req_id;
            $requests = $AmpGrievance->find('all', $options)->count();
            if ($requests > 0) {
                if ($status == '1') {
                    $queryInsert = $AmpGrievance->query();
                    $queryInsert->update()
                        ->set([
                            'rm_approval_status' => $status,
                            'rm_remark' => $remark,
                        ])
                        ->where(['id' => $req_id])
                        ->execute();
                } else {
                    $queryInsert = $AmpGrievance->query();
                    $queryInsert->update()
                        ->set([
                            'rm_approval_status' => $status,
                            'rm_remark' => $remark,
                            'status' => 'Rejected',
                        ])
                        ->where(['id' => $req_id])
                        ->execute();
                }

                $this->httpStatusCode = 200;
                $this->apiResponse['message'] = "Updated Successfully";
            } else {
                $this->httpStatusCode = 422;
                $this->apiResponse['message'] = "request not found";
            }

        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }
}
