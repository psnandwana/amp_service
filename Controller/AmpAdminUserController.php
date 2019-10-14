<?php
namespace App\Controller;
use RestApi\Controller\ApiController;
use Cake\I18n\Time;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use RestApi\Utility\JwtToken;

/**
 * AmpAdminUser Controller
 *
 * @property \App\Model\Table\AmpAdminUserTable $AmpAdminUser
 *
 * @method \App\Model\Entity\AmpAdminUser[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AmpAdminUserController extends ApiController
{
    public function index()
    {
        header("Access-Control-Allow-Origin: *");
        $page = $this->request->getData('page');
        $this->paginate = [ 'limit' => 1, 'page' => $page];
        $ampAdminUser = $this->paginate($this->AmpAdminUser);
        $this->apiResponse['data'] = $ampAdminUser;
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

    public function view($id = null)
    {
        $ampAdminUser = $this->AmpAdminUser->get($id, [
            'contain' => []
        ]);

        $this->set('ampAdminUser', $ampAdminUser);
    }

    public function add()
    {
        $ampAdminUser = $this->AmpAdminUser->newEntity();
        if ($this->request->is('post')) {
            $ampAdminUser = $this->AmpAdminUser->patchEntity($ampAdminUser, $this->request->getData());
            if ($this->AmpAdminUser->save($ampAdminUser)) {
                $this->Flash->success(__('The amp admin user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The amp admin user could not be saved. Please, try again.'));
        }
        $this->set(compact('ampAdminUser'));
    }

    public function edit($id = null)
    {
        $ampAdminUser = $this->AmpAdminUser->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $ampAdminUser = $this->AmpAdminUser->patchEntity($ampAdminUser, $this->request->getData());
            if ($this->AmpAdminUser->save($ampAdminUser)) {
                $this->Flash->success(__('The amp admin user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The amp admin user could not be saved. Please, try again.'));
        }
        $this->set(compact('ampAdminUser'));
    }


    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $ampAdminUser = $this->AmpAdminUser->get($id);
        if ($this->AmpAdminUser->delete($ampAdminUser)) {
            $this->Flash->success(__('The amp admin user has been deleted.'));
        } else {
            $this->Flash->error(__('The amp admin user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
