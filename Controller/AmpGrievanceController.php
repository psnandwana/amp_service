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
    
    public function index()
    {
        $ampGrievance = $this->paginate($this->AmpGrievance);

        $this->set(compact('ampGrievance'));
    }

    public function create()
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

    public function getrequesttype()
    {
        header("Access-Control-Allow-Origin: *");
        $status = array('Accomodation', 'Travel');
        $this->httpStatusCode = 200;
        $this->apiResponse['data'] = $status;
    }
    
}
