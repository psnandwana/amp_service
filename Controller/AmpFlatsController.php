<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\I18n\Time;
use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\Mailer\Email;
use RestApi\Controller\ApiController;

class AmpFlatsController extends ApiController
{

    public function index()
    {
        $ampFlats = $this->paginate($this->AmpFlats);
        $this->set(compact('ampFlats'));
    }

    public function create()
    {
        if ($this->checkToken()) {
            $AmpFlat = $this->AmpFlats->newEntity();
            $this->request->data['agreement_date'] = $this->customdateformat($this->request->data['agreement_date']);   
            $this->request->data['created_date'] = Time::now();           
            $AmpFlat = $this->AmpFlats->patchEntity($AmpFlat, $this->request->getData());
           
            if ($this->AmpFlats->save($AmpFlat)) {
                $this->httpStatusCode = 200;
                $this->apiResponse['message'] = 'Flat profile has been created successfully.';
            }else{
                $this->httpStatusCode = 422;
                $this->apiResponse['message'] = 'Unable to create Flat Profile.';
            }
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }
}
