<?php
namespace App\Controller;

use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\I18n\Time;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use RestApi\Controller\ApiController;

/**
 * AmpAdminUser Controller
 *
 * @property \App\Model\Table\AmpAdminUserTable $AmpAdminUser
 *
 * @method \App\Model\Entity\AmpAdminUser[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AmpAdminUserController extends ApiController
{
    public $front_url = 'https://www.indianpac.com/dashboard/';

    public function index()
    {
        if ($this->checkToken()) {
            header("Access-Control-Allow-Origin: *");
            $page = $this->request->getData('page');
            $user_id = $this->request->getData('user_id');
            $this->paginate = ['limit' => 10, 'page' => $page];
            $this->paginate['contain'] = ['AmpLocations'];
            $this->paginate['conditions'] = ['AmpAdminUser.id !=' => $user_id];
            $ampAdminUser = $this->paginate($this->AmpAdminUser);
            $numUsers = $this->AmpAdminUser->find('all', array('conditions' => array('id !=' => $user_id)))->count();

            $this->httpStatusCode = 200;
            $this->apiResponse['page'] = (int) $page;
            $this->apiResponse['total'] = (int) $numUsers;
            $this->apiResponse['users'] = $ampAdminUser;
            $this->apiResponse['message'] = "successfully fetched data";
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function login()
    {
        header("Access-Control-Allow-Origin: *");
        $userName = trim($this->request->getData('username'));
        $passwordTxt = $this->request->getData('password');
        $password = md5($passwordTxt);
        if (empty($userName)) {
            $this->httpStatusCode = 422;
            $this->apiResponse['message'] = 'Username is required';
        } else if (empty($passwordTxt)) {
            $this->httpStatusCode = 422;
            $this->apiResponse['message'] = 'Password is required';
        } else {
            $checkUser = $this->AmpAdminUser->find('all')->where(['email' => $userName, 'password' => $password])->first();
            if (!empty($checkUser)) {
                $checkUser = $checkUser->toArray();
                unset($checkUser['password']);
                $this->request->session()->write('admin_user_id', $checkUser['id']);
                $this->httpStatusCode = 200;
                $this->apiResponse['userinfo'] = $checkUser;
                $this->apiResponse['message'] = 'Login successfully';
            } else {
                $this->httpStatusCode = 422;
                $this->apiResponse['message'] = 'Invalid Username or Password';
            }
        }
    }

    /**Verify Token  */
    public function verifytoken()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            // if ($this->request->session()->check('admin_user_id')) {
            // $user_id = $this->request->session()->read('admin_user_id');
            $user_id = 46;
            $checkUser = $this->AmpAdminUser->find('all')->where(['id' => $user_id])->first();
            if (!empty($checkUser)) {
                $checkUser = $checkUser->toArray();
                unset($checkUser['password']);
                $this->request->session()->write('admin_user_id', $checkUser['id']);
                $this->httpStatusCode = 200;
                $this->apiResponse['userinfo'] = $checkUser;
                $this->apiResponse['message'] = 'successfully fetched data';
            } else {
                $this->httpStatusCode = 403;
                $this->apiResponse['message'] = "Please login again";
            }
            // } else {
            //     $this->httpStatusCode = 403;
            //     $this->apiResponse['message'] = "your session has been expired";
            // }
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    /** Logout API */
    public function logout()
    {
        header("Access-Control-Allow-Origin: *");
        $this->request->session()->delete('admin_user_id');
        $this->request->session()->destroy();
        $this->httpStatusCode = 200;
        $this->apiResponse['message'] = 'Logout successfully.';
    }

    /** Forget Password API */
    public function forgotpassword()
    {
        header("Access-Control-Allow-Origin: *");
        $connection = ConnectionManager::get('default');
        $email = $this->request->getData('email');
        if (!empty($email)) {
            $checkUser = $this->AmpAdminUser->find('all')->where(['email' => $email])->first();
            if (!empty($checkUser)) {
                $checkUser = $checkUser->toArray();
                $FirstName = $checkUser['name'];
                $otp = getToken(12);

                $dir = new Folder(WWW_ROOT . 'templates');
                $files = $dir->find('welcome.html', true);
                foreach ($files as $file) {
                    $file = new File($dir->pwd() . DS . $file);
                    $contents = $file->read();
                    $file->close();
                }
                $emails_content = $contents;

                $patterns = array();
                $outputs = preg_replace($patterns, '', $emails_content);
                $message = str_replace(array('{APP_NAME}', '{TITLE}', '{FIRSTNAME}', '{BODY}'),
                    array('Admin Management Portal', 'You have requested to reset your password', $FirstName, '<p style="text-align:justify;font-size: 14px;">We cannot simply send you your old password. A unique link to reset your password has been generated for you. To reset your password, click the following link and follow the instructions</p>
                         </br><p style="text-align:center"><a class="mailpoet_button" style="display: inline-block; -webkit-text-size-adjust: none; mso-hide: all; text-decoration: none; text-align: center; background-color: #41c1f2; border-radius: 11px; width: 218px; line-height: 40px; color: #ffffff; font-family: Verdana, Geneva, sans-serif; font-size: 18px; font-weight: normal; border: 1px solid #0ea8e4;" href="' . $this->front_url . 'password/create/' . $otp . '"> Reset Password </a></p>'), $outputs);
                $mail = new Email();

                $mail->transport('Gmail');

                $mail->emailFormat('html')
                    ->from(['info@indianpac.com' => 'Admin Management Portal'])
                    ->to([$email])
                    ->subject('Reset Your Password')
                    ->send($message);

                $checkOTP = $connection->execute("select * from amp_forgot_password where userid='" . $email . "'")->fetchAll('assoc');

                if (count($checkOTP) > 0) {
                    $connection->update('amp_forgot_password', ['secret_key' => $otp, 'is_updated' => 0], ['userid' => $email]);
                } else {
                    $connection->insert('amp_forgot_password', ['userid' => $email, 'secret_key' => $otp]);
                }
                $this->httpStatusCode = 200;
                $this->apiResponse['secret_key'] = $otp;
                $this->apiResponse['message'] = 'Reset password link has sent to your email address.';
            } else {
                $this->httpStatusCode = 422;
                $this->apiResponse['message'] = 'Oops! Your email is not registered with us.';
            }
        } else {
            $this->httpStatusCode = 422;
            $this->apiResponse['message'] = 'Please enter valid email address.';
        }
    }

    /** RESET PASSWORD API */
    public function resetpassword()
    {
        header("Access-Control-Allow-Origin: *");
        $this->request->allowMethod('post');
        $connection = ConnectionManager::get('default');
        $secret_key = $this->request->getData('secret_key');
        $password = md5($this->request->getData('password'));
        $amp_forgot_password = TableRegistry::get('amp_forgot_password');
        $getUser = $amp_forgot_password->find('all')->where(['secret_key' => $secret_key]);
        $getUser = $getUser->toList();
        if (!empty($getUser)) {
            $connection->update('amp_admin_user', ['password' => $password], ['userid' => $getUser[0]['userid']]);
            $this->httpStatusCode = 200;
            $this->apiResponse['message'] = 'Password has been reset successfully.';
        } else {
            $this->httpStatusCode = 422;
            $this->apiResponse['message'] = 'Your link has been expired.';
        }
    }

    /** Create user */
    public function createuser()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $data = $this->request->data;
            $name = $data['name'];
            $email = $data['email'];
            $password = $data['emp_code'];
            $campaign_office = $data['campaign_office'];
            $emp_code = $data['emp_code'];
            $mobile_no = $data['mobile'];
            $super_admin = $data['super_admin'];
            $admin = $data['admin'];
            $view = $data['view'];           
            $view_download = $data['view_download'];
            $rm = '0';
            if(isset($data['rm'])){
                $rm = $data['rm'];
            }
            $employee = $data['employee'];
            if ($super_admin == '1') {
                $admin = '1';
                $view = '1';
                $view_download = '1';
                $employee = '1';
                $rm = '1';
            }

            if (empty($email)) {

                $this->httpStatusCode = 422;
                $this->apiResponse['message'] = 'Email is required field.';

            } else if (empty($name)) {

                $this->httpStatusCode = 422;
                $this->apiResponse['message'] = 'Name is required field.';

            } else {

                $userList = $this->AmpAdminUser->find('all')->where(['email' => $email])->toArray();

                if (count($userList) > 0) {
                    $this->httpStatusCode = 422;
                    $this->apiResponse['message'] = 'Email already exist.';
                } else {
            
                    $queryInsert = $this->AmpAdminUser->query();
                    $queryInsert->insert(['name', 'email', 'campaign_office', 'emp_code', 'mobile_no', 'password', 'super_admin', 'admin' , 'employee', 'rm','view',  'view_download', 'created_date'])
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
                            'rm' => $rm,
                            'view' => $view,
                            'view_download' => $view_download,
                            'created_date' => Time::now(),
                        ])
                        ->execute();

                    if($employee == '1'){
                        $amp_employees_listing = TableRegistry::get('amp_employees_listing');
                        $empListing = $amp_employees_listing->find('all')->where(['email_id' => $email])->toArray();
                        if (count($empListing) > 0) {
                            $employeeID = $empListing[0]->id;
                            $queryEmpInsert = $amp_employees_listing->query();
                            $queryEmpInsert->update()
                                ->set([
                                    'emp_code' => $emp_code,
                                    'emp_name' => $name,
                                    'email_id' => $email
                                ])
                                ->where(['id' => $employeeID])
                                ->execute();
                                
                        }else{
                            $queryEmpInsert = $amp_employees_listing->query();
                            $statement = $queryEmpInsert->insert(['emp_code', 'emp_name', 'email_id', 'flat_band'])
                                ->values([
                                    'emp_code' => $emp_code,
                                    'emp_name' => $name,
                                    'email_id' => $email,                            
                                    'flat_band' => 8500
                                ])->execute();
                            $employeeID = $statement->lastInsertId('amp_employees_listing');
                        }

                        $queryUpdate =  $this->AmpAdminUser->query();
                        $queryUpdate->update()
                            ->set(['employee_id' => $employeeID])
                            ->where(['email' => $email])
                            ->execute();
                    }

                    $this->httpStatusCode = 200;
                    $this->apiResponse['message'] = 'New User has been created successfully.';
                }
            }
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    /** Get User  */
    public function getsingleuser()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $user_id = $this->request->getData('user_id');
            $checkUser = $this->AmpAdminUser->find('all')->where(['id' => $user_id])->first();
            if (!empty($checkUser)) {
                $checkUser = $checkUser->toArray();
                unset($checkUser['password']);

                $this->httpStatusCode = 200;
                $this->apiResponse['userinfo'] = $checkUser;
                $this->apiResponse['message'] = 'successfully fetched data';
            } else {
                $this->httpStatusCode = 403;
                $this->apiResponse['message'] = "No user found";
            }
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    /** Update user */
    public function updateuser()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $data = $this->request->data;

            $user_id = $data['user_id'];
            $name = $data['name'];
            $email = $data['email'];
            $campaign_office = $data['campaign_office'];
            $emp_code = $data['emp_code'];
            $mobile_no = $data['mobile'];
            $super_admin = $data['super_admin'];
            $admin = $data['admin'];
            $view = $data['view'];
            $view_download = $data['view_download'];
            $employee = $data['employee'];
            if(isset($data['rm'])){
                $rm = $data['rm'];
            }           
            if ($super_admin == '1') {
                $admin = '1';
                $view = '1';
                $view_download = '1';
                $employee = '1';
                $rm = '1';
            }

            if (empty($email)) {

                $this->httpStatusCode = 422;
                $this->apiResponse['message'] = 'Email is required field.';

            } else if (empty($name)) {

                $this->httpStatusCode = 422;
                $this->apiResponse['message'] = 'Name is required field.';

            } else {

                $userList = $this->AmpAdminUser->find('all')->where(['email' => $email,'id !='  => $user_id])->toArray();
                if (count($userList) > 0) {
                    $this->httpStatusCode = 422;
                    $this->apiResponse['message'] = 'Email already exist.';
                } else {
                    $queryUpdate = $this->AmpAdminUser->query();
                    $queryUpdate->update()
                        ->set([
                            'name' => $name,
                            'email' => $email,
                            'campaign_office' => $campaign_office,
                            'emp_code' => $emp_code,
                            'mobile_no' => $mobile_no,
                            'super_admin' => $super_admin,
                            'admin' => $admin,
                            'employee' => $employee,
                            'rm' => $rm,
                            'view' => $view,
                            'view_download' => $view_download
                        ])
                        ->where(['id' => $user_id])
                        ->execute();
                    
                    if($employee == '1'){
                        $amp_employees_listing = TableRegistry::get('amp_employees_listing');
                        $empListing = $amp_employees_listing->find('all')->where(['email_id' => $email])->toArray();
                        if (count($empListing) > 0) {
                            $employeeID = $empListing[0]->id;
                            $queryEmpInsert = $amp_employees_listing->query();
                            $queryEmpInsert->update()
                                ->set([
                                    'emp_code' => $emp_code,
                                    'emp_name' => $name,
                                    'email_id' => $email
                                ])
                                ->where(['id' => $employeeID])
                                ->execute();
                                
                        }else{
                            $queryEmpInsert = $amp_employees_listing->query();
                            $statement = $queryEmpInsert->insert(['emp_code', 'emp_name', 'email_id', 'flat_band'])
                                ->values([
                                    'emp_code' => $emp_code,
                                    'emp_name' => $name,
                                    'email_id' => $email,                            
                                    'flat_band' => 8500
                                ])->execute();
                            $employeeID = $statement->lastInsertId('amp_employees_listing');
                        }

                        $queryEmpIDUpdate =  $this->AmpAdminUser->query();
                        $queryEmpIDUpdate->update()
                            ->set(['employee_id' => $employeeID])
                            ->where(['id' => $user_id])
                            ->execute();
                    }

                    $this->httpStatusCode = 200;
                    $this->apiResponse['message'] = 'User has been updated successfully.';
                }
            }
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    /**
     *  Get Location API
     */
    public function getlocation()
    {
        header("Access-Control-Allow-Origin: *");
        $options = array();
        $options['fields'] = array('id', 'location');

        $TblLocation = TableRegistry::get('Location', ['table' => 'amp_locations']);
        $location = $TblLocation->find('all', $options)->toArray();
        $this->httpStatusCode = 200;
        $this->apiResponse['locations'] = $location;
    }

    /** Delete user */
    public function deleteuser()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $this->request->allowMethod('post');
            $data = $this->request->data;
            $user_id = $data['user_id'];
            $entity = $this->AmpAdminUser->get($user_id);
            $result = $this->AmpAdminUser->delete($entity);
            $this->httpStatusCode = 200;
            $this->apiResponse['message'] = 'Deleted successfully.';
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

}

function crypto_rand_secure($min, $max)
{
    $range = $max - $min;
    if ($range < 1) {
        return $min;
    }
    // not so random...
    $log = ceil(log($range, 2));
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd > $range);
    return $min + $rnd;
}

function getToken($length)
{
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet .= "0123456789";
    $max = strlen($codeAlphabet); // edited

    for ($i = 0; $i < $length; $i++) {
        $token .= $codeAlphabet[crypto_rand_secure(0, $max - 1)];
    }
    return $token;
}
