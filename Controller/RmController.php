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
            $request_type = $this->request->getData('request_type');
            $AmpGrievance = TableRegistry::get('Grievance', ['table' => 'amp_grievance']);
            $options = array();
            $options['conditions']['rm_id'] = $rm_id;
            $options['conditions']['request_type'] = $request_type;
            // All Requests
            $allRequests = $AmpGrievance->find('all', $options)->count();
            // Pending Requests 
            $options['conditions']['rm_approval_status'] = "0";
            $pendingRequests = $AmpGrievance->find('all', $options)->count();
            // Approved Requests
            $options['conditions']['status'] = "Resolved";
            $approvedRequests = $AmpGrievance->find('all', $options)->count();
            // Rejected Requests
            $options['conditions']['status'] = "Rejected";
            $rejectedRequests = $AmpGrievance->find('all', $options)->count();
            $this->httpStatusCode = 200;
            $this->apiResponse['all'] = $allRequests;
            $this->apiResponse['pending'] = $pendingRequests;
            $this->apiResponse['resolved'] = $approvedRequests;
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
            $page = $this->request->getData('page');
            $limit = 10;
            $start = ($page - 1) * $limit;

            $rm_id = $this->request->getData('rm_id');
            $request_type = $this->request->getData('request_type');
            $type = $this->request->getData('type');
            $type = strtolower($type);
            $options = array();
            $options['conditions']['rm_id'] = $rm_id;
            $options['conditions']['request_type'] = $request_type;
            
            $options = ['limit' => 10, 'page' => $page];
            switch($type){
                case 'pending':
                    $options['conditions']['rm_approval_status'] = '0';
                    break;
                case 'resolved':
                    $options['conditions']['rm_approval_status !='] = '0';
                    $options['conditions']['status'] = 'Resolved';
                    break;
                case 'rejected':
                    $options['conditions']['rm_approval_status !='] = '0';
                    $options['conditions']['status'] = 'Rejected';
                    break;
            }
            $options['fields'] = array(                
                'id' => 'Grievance.id',
                'subject',
                'request_type',                
                'description',
                'status' =>'Grievance.status',
                'submitted_date' => 'Grievance.submitted_date',
                'employee__id' => 'Employee.id',
                'employee__emp_code' => 'Employee.emp_code',
                'employee__emp_name' => 'Employee.emp_name',
                'employee__email_id' => 'Employee.email_id',
                'employee__flat_band' => 'Employee.flat_band',
                'employee__flat_no' => 'Flat.flat_no',
                'employee__apartment_name' => 'Flat.apartment_name',
                'reporting_manager__id' => 'RM.id',
                'reporting_manager__emp_code' => 'RM.emp_code',
                'reporting_manager__emp_name' => 'RM.emp_name',
                'reporting_manager__email_id' => 'RM.email_id',
                'reporting_manager__flat_band' => 'RM.flat_band',  
                'reporting_manager__flat_no' => 'RMFlat.flat_no',
                'reporting_manager__apartment_name' => 'RMFlat.apartment_name'      
            );

            $options['join'] = array(
                array(
                    'table' => 'amp_employees_listing',
                    'alias' => 'Employee',
                    'type' => 'INNER',
                    'conditions' => 'Grievance.employee_id = Employee.id',
                ),
                array(
                    'table' => 'amp_employees_listing',
                    'alias' => 'RM',
                    'type' => 'INNER',
                    'conditions' => 'RM.email_id = Employee.rm_email_id',
                ),
                array(
                    'table' => 'amp_room_employee_mapping',
                    'alias' => 'RoomEmpMap',
                    'type' => 'LEFT',
                    'conditions' => ['RoomEmpMap.employee_id = Employee.id', 'RoomEmpMap.active_status' => '1'],
                ),  
                array(
                    'table' => 'amp_flats',
                    'alias' => 'Flat',
                    'type' => 'LEFT',
                    'conditions' => ['Flat.id = RoomEmpMap.flat_id'],
                ),
                array(
                    'table' => 'amp_room_employee_mapping',
                    'alias' => 'RMRoomEmpMap',
                    'type' => 'LEFT',
                    'conditions' => ['RMRoomEmpMap.employee_id = RM.id', 'RMRoomEmpMap.active_status' => '1'],
                ),  
                array(
                    'table' => 'amp_flats',
                    'alias' => 'RMFlat',
                    'type' => 'LEFT',
                    'conditions' => ['RMFlat.id = RMRoomEmpMap.flat_id'],
                )           
            );

            $options['limit'] = $limit;
            $options['order'] = 'submitted_date DESC';
            $options['offset'] = $start;

            $AmpGrievance = $AmpGrievance->find('all', $options)->toArray();
            if (count($AmpGrievance) > 0) {
                foreach ($AmpGrievance as $index => $request) {
                    $reporting_manager = $request['reporting_manager'];
                    unset($request['reporting_manager']);
                    $AmpGrievance[$index]['employee']['reporting_manager'] = $reporting_manager;
                    $AmpGrievance[$index]['submitted_date'] = date("jS F, Y", strtotime($request['submitted_date']));
                }
            }

            $this->httpStatusCode = 200;
            $this->apiResponse['page'] = (int) $page;
            $this->apiResponse['requests'] = $AmpGrievance;
            $this->apiResponse['message'] = "successfully fetched data";
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function getrmapprovalstatus()
    {
        header("Access-Control-Allow-Origin: *");
        $status[] = array('value' => '1', 'status' => 'Approve');
        $status[] = array('value' => '2', 'status' => 'Reject');
        $this->httpStatusCode = 200;
        $this->apiResponse['data'] = $status;
    }

    public function changestatus()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $AmpGrievance = TableRegistry::get('Grievance', ['table' => 'amp_grievance']);
            $request_id = $this->request->getData('request_id');
            $status = $this->request->getData('status');
            $remark = $this->request->getData('remark');
            date_default_timezone_set('Asia/Kolkata');
            $current_date = date('Y-m-d H:i:s');

            $options = array();
            $options['conditions']['id'] = $request_id;
            $requests = $AmpGrievance->find('all', $options)->count();
            if ($requests > 0) {
                if ($status == '1') {
                    $queryUpdate = $AmpGrievance->query();
                    $queryUpdate->update()
                        ->set([
                            'rm_approval_status' => $status,
                            'rm_remark' => $remark,
                            'rm_approval_date' => $current_date
                        ])
                        ->where(['id' => $request_id])
                        ->execute();
                } else {
                    $queryUpdate = $AmpGrievance->query();
                    $queryUpdate->update()
                        ->set([
                            'rm_approval_status' => $status,
                            'rm_remark' => $remark,
                            'status' => 'Rejected',
                            'rm_approval_date' => $current_date
                        ])
                        ->where(['id' => $request_id])
                        ->execute();
                }

                $this->httpStatusCode = 200;
                $this->apiResponse['message'] = "Request has been approved successfully.";
            } else {
                $this->httpStatusCode = 422;
                $this->apiResponse['message'] = "Request not found";
            }

        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }
}
