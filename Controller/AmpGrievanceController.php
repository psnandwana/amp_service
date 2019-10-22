<?php
namespace App\Controller;

use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\I18n\Time;
use Cake\Mailer\Email;
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
           
            $this->request->data['submitted_date'] = $current_date;
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
        $totalRequests = $this->AmpGrievance->find('all', array('conditions' => array('employee_id' => $employee_id)))->count();
    }

    public function requesttypes()
    {
        header("Access-Control-Allow-Origin: *");
        $status = array('Accomodation', 'Travel');
        $this->httpStatusCode = 200;
        $this->apiResponse['data'] = $status;
    }

    public function list()
    {
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
            if($type != 'all'){
                $this->paginate['conditions']['status'] = ucfirst($type);
                $options['conditions']['status'] = ucfirst($type);
            }            
            
            $AmpGrievance = $this->paginate($this->AmpGrievance)->toArray();
            $totalRequests = $this->AmpGrievance->find('all', $options)->count();
            if(count($AmpGrievance) > 0){
                foreach ($AmpGrievance as $index => $request) {
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

    // public function getpendingrequest()
    // {
    //     header("Access-Control-Allow-Origin: *");
    //     if ($this->checkToken()) {            
    //         $page = $this->request->getData('page');
    //         $employee_id = $this->request->getData('employee_id');
    //         $this->paginate = ['limit' => 10, 'page' => $page];
    //         $this->paginate['conditions']['employee_id'] = $employee_id;
    //         $this->paginate['conditions']['status'] = 'Pending';
    //         $AmpGrievance = $this->paginate($this->AmpGrievance)->toArray();
    //         $totalRequests = $this->AmpGrievance->find('all', array('conditions' => array('employee_id' => $employee_id,'status' => 'Pending')))->count();
            
    //         if(count($AmpGrievance) > 0){
    //             foreach ($AmpGrievance as $index => $request) {
    //                 $AmpGrievance[$index]['submitted_date'] = date("jS F, Y", strtotime($request['submitted_date']));
    //             }   
    //         }    

    //         $this->httpStatusCode = 200;
    //         $this->apiResponse['page'] = (int) $page;
    //         $this->apiResponse['total'] = (int) $totalRequests;
    //         $this->apiResponse['requests'] = $AmpGrievance;
    //         $this->apiResponse['message'] = "successfully fetched data";
    //     } else {
    //         $this->httpStatusCode = 403;
    //         $this->apiResponse['message'] = "your session has been expired";
    //     }
    // }

    // public function getresolvedrequest()
    // {
    //     header("Access-Control-Allow-Origin: *");
    //     if ($this->checkToken()) {            
    //         $page = $this->request->getData('page');
    //         $employee_id = $this->request->getData('employee_id');
    //         $this->paginate = ['limit' => 10, 'page' => $page];
    //         $this->paginate['conditions']['employee_id'] = $employee_id;
    //         $this->paginate['conditions']['status'] = 'Resolved';
    //         $AmpGrievance = $this->paginate($this->AmpGrievance)->toArray();
    //         $totalRequests = $this->AmpGrievance->find('all', array('conditions' => array('employee_id' => $employee_id,'status' => 'Resolved')))->count();
            
    //         if(count($AmpGrievance) > 0){
    //             foreach ($AmpGrievance as $index => $request) {
    //                 $AmpGrievance[$index]['submitted_date'] = date("jS F, Y", strtotime($request['submitted_date']));
    //             }   
    //         }    

    //         $this->httpStatusCode = 200;
    //         $this->apiResponse['page'] = (int) $page;
    //         $this->apiResponse['total'] = (int) $totalRequests;
    //         $this->apiResponse['requests'] = $AmpGrievance;
    //         $this->apiResponse['message'] = "successfully fetched data";
    //     } else {
    //         $this->httpStatusCode = 403;
    //         $this->apiResponse['message'] = "your session has been expired";
    //     }
    // }
    
}
