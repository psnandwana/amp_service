<?php
namespace App\Controller;

use Cake\ORM\TableRegistry;
use RestApi\Controller\ApiController;

class AmpGrievanceController extends ApiController
{

    public function add()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            date_default_timezone_set('Asia/Kolkata');
            $current_date = date('Y-m-d H:i:s');
            $AmpGrievance = $this->AmpGrievance->newEntity();
            $EmployeeTable = TableRegistry::get('Employee', ['table' => 'amp_employees_listing']);
            $options['conditions']['Employee.id'] = $this->request->getData('employee_id');
            $options['fields'] = array('rm_id'=>'Admin.id','rm_email' =>'Admin.email');
            $options['join'] = array(
                array(
                    'table' => 'amp_admin_user',
                    'alias' => 'Admin',
                    'type' => 'INNER',
                    'conditions' => 'Admin.email = Employee.rm_email_id',
                )
            );
            $RMDetails = $EmployeeTable->find('all', $options)->first()->toArray();
            $rm_id = $RMDetails['rm_id'];
            $rm_email = $RMDetails['rm_email'];

            $this->request->data['submitted_date'] = $current_date;
            $this->request->data['rm_id'] = $rm_id;
            $this->request->data['status'] = 'Pending';
            $AmpGrievance = $this->AmpGrievance->patchEntity($AmpGrievance, $this->request->getData());
            if ($this->AmpGrievance->save($AmpGrievance)) {
                $this->httpStatusCode = 200;
                $this->apiResponse['message'] = 'Your request has been submitted successfully';
            } else {
                $this->httpStatusCode = 422;
                $this->apiResponse['message'] = 'Unable to submit your request';
            }
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function count()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $employee_id = $this->request->getData('employee_id');
            $request_type = $this->request->getData('request_type');
            $options = array();
            $options['conditions']['employee_id'] = $employee_id;
            $options['conditions']['request_type'] = $request_type;
            $allRequests = $this->AmpGrievance->find('all', $options)->count();
            $options['conditions']['rm_approval_status'] = "0";
            $rmPendingRequests = $this->AmpGrievance->find('all', $options)->count();
            $options['conditions']['rm_approval_status !='] = "0";
            $options['conditions']['status'] = "Resolved";
            $resolvedRequests = $this->AmpGrievance->find('all', $options)->count();
            $options['conditions']['status'] = "Pending";
            $pendingRequests = $this->AmpGrievance->find('all', $options)->count();
            $options['conditions']['status'] = "Rejected";
            $rejectedRequests = $this->AmpGrievance->find('all', $options)->count();
            $this->httpStatusCode = 200;
            $this->apiResponse['all'] = $allRequests;
            $this->apiResponse['rm_pending'] = $rmPendingRequests;
            $this->apiResponse['pending'] = $pendingRequests;
            $this->apiResponse['resolved'] = $resolvedRequests;         
            $this->apiResponse['rejected'] = $rejectedRequests;            
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function requesttypes()
    {
        header("Access-Control-Allow-Origin: *");
        $status = array('Accomodation', 'Travel');
        $this->httpStatusCode = 200;
        $this->apiResponse['data'] = $status;
    }

    function list() {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $page = $this->request->getData('page');
            $employee_id = $this->request->getData('employee_id');
            $type = $this->request->getData('type');
            $type = strtolower($type);
            $options = array();
            $options['conditions']['employee_id'] = $employee_id;
            $this->paginate['conditions']['employee_id'] = $employee_id;
            $this->paginate = ['limit' => 10, 'page' => $page];
            if ($type != 'all') {
                $this->paginate['conditions']['status'] = ucfirst($type);
                $options['conditions']['status'] = ucfirst($type);
            }

            $AmpGrievance = $this->paginate($this->AmpGrievance)->toArray();
            $totalRequests = $this->AmpGrievance->find('all', $options)->count();
            if (count($AmpGrievance) > 0) {
                foreach ($AmpGrievance as $index => $request) {
                    unset($AmpGrievance[$index]['employee_id']);
                    $AmpGrievance[$index]['submitted_date'] = date("jS F, Y", strtotime($request['submitted_date']));
                }
            }

            $this->httpStatusCode = 200;
            $this->apiResponse['page'] = (int) $page;
            $this->apiResponse['total'] = (int) $totalRequests;
            $this->apiResponse['requests'] = $AmpGrievance;
            $this->apiResponse['message'] = "successfully fetched data";
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }
}
