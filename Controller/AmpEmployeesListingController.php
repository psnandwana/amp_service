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
    
    // public function index()
    // {
    //     $this->paginate = [
    //         'contain' => ['Emails']
    //     ];
    //     $ampEmployeesListing = $this->paginate($this->AmpEmployeesListing);

    //     $this->set(compact('ampEmployeesListing'));
    // }

    /**List employee List */
    public function index()
    {
        if ($this->checkToken()) {
            header("Access-Control-Allow-Origin: *");
            $ampEmployeesListing = $this->AmpEmployeesListing->find('all')->toList;
            $this->httpStatusCode = 200;
            $this->apiResponse['employeeinfo'] = $ampEmployeesListing;
        } else{
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    // /**
    //  * Add method
    //  *
    //  * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
    //  */
    // public function add()
    // {
    //     $ampEmployeesListing = $this->AmpEmployeesListing->newEntity();
    //     if ($this->request->is('post')) {
    //         $ampEmployeesListing = $this->AmpEmployeesListing->patchEntity($ampEmployeesListing, $this->request->getData());
    //         if ($this->AmpEmployeesListing->save($ampEmployeesListing)) {
    //             $this->Flash->success(__('The amp employees listing has been saved.'));

    //             return $this->redirect(['action' => 'index']);
    //         }
    //         $this->Flash->error(__('The amp employees listing could not be saved. Please, try again.'));
    //     }
    //     $emails = $this->AmpEmployeesListing->Emails->find('list', ['limit' => 200]);
    //     $this->set(compact('ampEmployeesListing', 'emails'));
    // }
   
}
