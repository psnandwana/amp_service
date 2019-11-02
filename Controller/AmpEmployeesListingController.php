<?php
namespace App\Controller;

use Cake\ORM\TableRegistry;
use RestApi\Controller\ApiController;

class AmpEmployeesListingController extends ApiController
{
    /**List employee List */
    public function index()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $employee_listing = TableRegistry::get('employee', ['table' => 'amp_employees_listing']);
            $page = $this->request->getData('page');
            $limit = 10;
            $start = ($page - 1) * $limit;
            $name = $this->request->getData('emp_name');
            // $this->paginate = ['limit' => 10, 'page' => $page];
            // $ampEmployeesListing = $this->paginate($this->AmpEmployeesListing);
            $numUsers = $employee_listing->find('all')->count();
            $options = array();
            if ($name != "") {
                $options['conditions']['employee.emp_name'] = $name;
            }

            $options['join'] = array(
                // array(
                //     'table' => 'amp_admin_user',
                //     'alias' => 'adminRm',
                //     'type' => 'INNER',
                //     'conditions' => ['adminUser.email = employee.rm_email_id']
                // ),
                array(
                    'table' => 'amp_admin_user',
                    'alias' => 'adminUser',
                    'type' => 'INNER',
                    'conditions' => ['adminUser.email = employee.rm_email_id']
                ),
            );

            $options['fields'] = array(
                'employee.id',
                'employee.emp_code',
                'employee.emp_name',
                'employee.email_id',
                'employee.flat_band',
                'employee.rm_email_id',
                'employee.team',
                // 'employee.phone',
                // 'employee.acco_model_name',
                'rm_name' => 'adminUser.name',
            );

            $options['limit'] = $limit;
            $options['offset'] = $start;
            $ampEmployeesListing = $employee_listing->find('all', $options)->toArray();

            // dd($ampEmployeesListing);
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

    public function uploademployees()
    {
        $data = $this->request->data['upload_employees'];
        date_default_timezone_set('Asia/Kolkata');
        $current_date = date('Y-m-d H:i:s');
        if (isset($data['name'])) {
            $file = $data['tmp_name'];
            $handle = fopen($file, "r");
            $headerLine = true;
            $AmpAdminUser = TableRegistry::get('amp_admin_user');
            $amp_employees_listing = TableRegistry::get('amp_employees_listing');
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                if ($headerLine) {$headerLine = false;} else {
                    $name = $row[1];
                    $emp_code = $row[2];
                    $password = $row[2];
                    $email = $row[3];
                    $campaign_office = $row[8];
                    $mobile_no = null;
                    $rm_email_id = $row[6];
                    $flat_band = $row[9];
                    if ($flat_band == '') {
                        $flat_band = 8500;
                    }
                    $super_admin = 0;
                    $admin = 0;
                    $view = 0;
                    $view_download = 0;
                    $employee = 1;

                    $userList = $AmpAdminUser->find('all')->where(['email' => $email])->toArray();

                    if (count($userList) == 0) {
                        $queryInsert = $AmpAdminUser->query();
                        $queryInsert->insert(['name', 'email', 'campaign_office', 'emp_code', 'mobile_no', 'password', 'super_admin', 'admin', 'employee', 'view', 'view_download', 'created_date'])
                            ->values([
                                'name' => $name,
                                'email' => $email,
                                'campaign_office' => $campaign_office,
                                'emp_code' => $emp_code,
                                'mobile_no' => $mobile_no,
                                'password' => md5($password),
                                'super_admin' => $super_admin,
                                'admin' => $admin,
                                'employee' => $employee,
                                'view' => $view,
                                'view_download' => $view_download,
                                'created_date' => $current_date,
                            ])
                            ->execute();

                        $empListing = $amp_employees_listing->find('all')->where(['email_id' => $email])->toArray();
                        if (count($empListing) > 0) {
                            $employeeID = $empListing[0]->id;
                            $queryEmpInsert = $amp_employees_listing->query();
                            $queryEmpInsert->update()
                                ->set([
                                    'emp_code' => $emp_code,
                                    'emp_name' => $name,
                                    'email_id' => $email,
                                    'flat_band' => $flat_band,
                                    'rm_email_id' => $rm_email_id,
                                ])
                                ->where(['id' => $employeeID])
                                ->execute();

                        } else {
                            $queryEmpInsert = $amp_employees_listing->query();
                            $statement = $queryEmpInsert->insert(['emp_code', 'emp_name', 'email_id', 'flat_band', 'rm_email_id'])
                                ->values([
                                    'emp_code' => $emp_code,
                                    'emp_name' => $name,
                                    'email_id' => $email,
                                    'flat_band' => $flat_band,
                                    'rm_email_id' => $rm_email_id,
                                ])->execute();
                            $employeeID = $statement->lastInsertId('amp_employees_listing');
                        }

                        $queryUpdate = $AmpAdminUser->query();
                        $queryUpdate->update()
                            ->set(['employee_id' => $employeeID])
                            ->where(['email' => $email])
                            ->execute();
                    }
                }
            }
            fclose($handle);
            $this->httpStatusCode = 200;
            $this->apiResponse['message'] = 'CSV has been uploaded successfully.';
        } else {
            $this->httpStatusCode = 422;
            $this->apiResponse['message'] = "Please upload proper CSV";
        }
    }
}
