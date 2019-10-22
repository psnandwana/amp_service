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

    public function getrequesttype()
    {
        header("Access-Control-Allow-Origin: *");
        $status = array('Accomodation', 'Travel');
        $this->httpStatusCode = 200;
        $this->apiResponse['status'] = $status;
    }
    
}
