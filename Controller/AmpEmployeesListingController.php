<?php
namespace App\Controller;

use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\I18n\Time;
use Cake\Mailer\Email;
use RestApi\Controller\ApiController;


class AmpEmployeesListingController extends ApiController
{
    /**List employee List */
    public function index()
    {
        if ($this->checkToken()) {
            header("Access-Control-Allow-Origin: *");
            $ampEmployeesListing = $this->AmpEmployeesListing->find('all')->toList();
            $this->httpStatusCode = 200;
            $this->apiResponse['employeeinfo'] = $ampEmployeesListing;
        } else{
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    //Auto Search
    public function filteremployee()
    {
        header("Access-Control-Allow-Origin: *");
        $options = array();
        $emp_code = $this->request->getData('emp_code');
        $options['conditions'] = array('emp_code LIKE'=> $emp_code.'%');
        $employees = $this->AmpEmployeesListing->find('all',$options)->toArray();
        $tmp_array = array();
        foreach ($employees as $value) {
            $tmp_array[] = $value;
        }
        $this->httpStatusCode = 200;
        $this->apiResponse['employees'] = $tmp_array;
    }
    
}
