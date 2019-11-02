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
            if ($employee_id!=''){
                $options['conditions']['employee_id'] = $employee_id;
            }
            $options['conditions']['request_type'] = $request_type;
            $allRequests = $this->AmpGrievance->find('all', $options)->count();
            
            $options['conditions']['status'] = "Resolved";
            $resolvedRequests = $this->AmpGrievance->find('all', $options)->count();
            $options['conditions']['status'] = "Pending";
            $pendingRequests = $this->AmpGrievance->find('all', $options)->count();
            $options['conditions']['status'] = "Rejected";
            $rejectedRequests = $this->AmpGrievance->find('all', $options)->count();
            if ($request_type=='Travel'){
                $options['conditions']['rm_approval_status'] = "0";
                $rmPendingRequests = $this->AmpGrievance->find('all', $options)->count();
                $options['conditions']['rm_approval_status !='] = "0";
                $rmPendingRequests = $this->AmpGrievance->find('all', $options)->count();
                $this->apiResponse['rm_pending'] = $rmPendingRequests;
            }
            $this->httpStatusCode = 200;
            $this->apiResponse['all'] = $allRequests;
            
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
        $status = array('Accommodation', 'Travel');
        $this->httpStatusCode = 200;
        $this->apiResponse['data'] = $status;
    }

    function list() {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $page = $this->request->getData('page');
            $employee_id = $this->request->getData('employee_id');
            $request_type = $this->request->getData('request_type');
            $type = $this->request->getData('type');
            $type = strtolower($type);
            if ($employee_id!=''){
                $options['conditions']['employee_id'] = $employee_id;
            }
            // $this->paginate['conditions']['employee_id'] = $employee_id;
            $this->paginate['conditions']['request_type'] = $request_type;
            $options = array();
            $options['conditions']['employee_id'] = $employee_id;
            $options['conditions']['request_type'] = $request_type;
            $this->paginate = ['limit' => 10, 'page' => $page];
            switch($type){
                case 'pending_rm':
                    $this->paginate['conditions']['rm_approval_status'] = '0';
                    $options['conditions']['rm_approval_status'] = '0';
                    break;
                case 'pending':
                    // $this->paginate['conditions']['rm_approval_status !='] = '0';
                    // $this->paginate['conditions']['status'] = 'Pending';
                    $options['conditions']['rm_approval_status !='] = '0';
                    $options['conditions']['status'] = 'Pending';
                    break;
                case 'resolved':
                    // $this->paginate['conditions']['rm_approval_status !='] = '0';
                    // $this->paginate['conditions']['status'] = 'Resolved';
                    $options['conditions']['rm_approval_status !='] = '0';
                    $options['conditions']['status'] = 'Resolved';
                    break;
                case 'rejected':
                    // $this->paginate['conditions']['rm_approval_status !='] = '0';
                    // $this->paginate['conditions']['status'] = 'Rejected';
                    $options['conditions']['rm_approval_status !='] = '0';
                    $options['conditions']['status'] = 'Rejected';
                    break;
            }
            $totalRequests = $this->AmpGrievance->find('all', $options)->count();
            dd($totalRequests);
            $this->paginate['fields'] = array(                
                'id' => 'AmpGrievance.id',
                'subject',
                'request_type',                
                'description',
                'status' =>'AmpGrievance.status',
                'submitted_date' => 'AmpGrievance.submitted_date',
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

            $this->paginate['join'] = array(
                array(
                    'table' => 'amp_employees_listing',
                    'alias' => 'Employee',
                    'type' => 'INNER',
                    'conditions' => 'AmpGrievance.employee_id = Employee.id',
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
            $AmpGrievance = $this->paginate($this->AmpGrievance)->toArray();
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
            $this->apiResponse['total'] = (int) $totalRequests;
            $this->apiResponse['requests'] = $AmpGrievance;
            $this->apiResponse['message'] = "successfully fetched data";
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function requestcount()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $request_type = $this->request->getData('request_type');
            $options = array();
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

    function getallrequests() {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $page = $this->request->getData('page');
            $request_type = $this->request->getData('request_type');
            $type = $this->request->getData('type');
            $type = strtolower($type);
            $this->paginate['conditions']['request_type'] = $request_type;
            $options = array();
            $options['conditions']['request_type'] = $request_type;
            $this->paginate = ['limit' => 10, 'page' => $page];
            switch($type){
                case 'pending_rm':
                    $this->paginate['conditions']['rm_approval_status'] = '0';
                    $options['conditions']['rm_approval_status'] = '0';
                    break;
                case 'pending':
                    $this->paginate['conditions']['rm_approval_status !='] = '0';
                    $this->paginate['conditions']['status'] = 'Pending';
                    $options['conditions']['rm_approval_status !='] = '0';
                    $options['conditions']['status'] = 'Pending';
                    break;
                case 'resolved':
                    $this->paginate['conditions']['rm_approval_status !='] = '0';
                    $this->paginate['conditions']['status'] = 'Resolved';
                    $options['conditions']['rm_approval_status !='] = '0';
                    $options['conditions']['status'] = 'Resolved';
                    break;
                case 'rejected':
                    $this->paginate['conditions']['rm_approval_status !='] = '0';
                    $this->paginate['conditions']['status'] = 'Rejected';
                    $options['conditions']['rm_approval_status !='] = '0';
                    $options['conditions']['status'] = 'Rejected';
                    break;
            }
            $this->paginate['fields'] = array(                
                'id' => 'AmpGrievance.id',
                'subject',
                'request_type',                
                'description',
                'status' =>'AmpGrievance.status',
                'submitted_date' => 'AmpGrievance.submitted_date',
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

            $this->paginate['join'] = array(
                array(
                    'table' => 'amp_employees_listing',
                    'alias' => 'Employee',
                    'type' => 'INNER',
                    'conditions' => 'AmpGrievance.employee_id = Employee.id',
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
            $AmpGrievance = $this->paginate($this->AmpGrievance)->toArray();
            if (count($AmpGrievance) > 0) {
                foreach ($AmpGrievance as $index => $request) {
                    $reporting_manager = $request['reporting_manager'];
                    unset($request['reporting_manager']);
                    $AmpGrievance[$index]['employee']['reporting_manager'] = $reporting_manager;
                    $AmpGrievance[$index]['submitted_date'] = date("jS F, Y", strtotime($request['submitted_date']));
                }
            }

            $totalRequests = $this->AmpGrievance->find('all', $options)->count();
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

    public function getadminapprovalstatus()
    {
        header("Access-Control-Allow-Origin: *");
        $status[] = array('value' => 'Resolved', 'status' => 'Resolve');
        $status[] = array('value' => 'Rejected', 'status' => 'Reject');
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
                $queryUpdate = $AmpGrievance->query();
                $queryUpdate->update()
                    ->set([
                        'status' => $status,
                        'admin_remark' => $remark,
                        'approved_date' => $current_date
                    ])
                    ->where(['id' => $request_id])
                    ->execute();

                $this->httpStatusCode = 200;
                $this->apiResponse['message'] = "Request status has been updated successfully.";
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
