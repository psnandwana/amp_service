<?php
namespace App\Controller;

use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\I18n\Time;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use RestApi\Controller\ApiController;

class AmpEmployeesListingController extends ApiController
{
    /**List employee List */
    public function index()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $page = $this->request->getData('page');
            $this->paginate = ['limit' => 10, 'page' => $page];
            $ampEmployeesListing = $this->paginate($this->AmpEmployeesListing);
            $numUsers = $this->AmpEmployeesListing->find('all')->count();

            $this->httpStatusCode = 200;
            $this->apiResponse['page'] = (int) $page;
            $this->apiResponse['total'] = (int) $numUsers;
            $this->apiResponse['employees'] = $ampEmployeesListing;
            $this->apiResponse['message'] = "successfully fetched data";
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    //Auto Search
    public function filteremployee()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $options = array();
            $emp_code = $this->request->getData('emp_code');
            $options['conditions'] = array('emp_code LIKE' => $emp_code . '%');
            $employees = $this->AmpEmployeesListing->find('all', $options)->toArray();
            $tmp_array = array();
            foreach ($employees as $value) {
                $tmp_array[] = $value;
            }
            $this->httpStatusCode = 200;
            $this->apiResponse['employees'] = $tmp_array;
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }
}
