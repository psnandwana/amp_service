<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\I18n\Time;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use RestApi\Controller\ApiController;

class AmpFlatsController extends ApiController
{

    public function index()
    {
        if ($this->checkToken()) {
            header("Access-Control-Allow-Origin: *");
            $page = $this->request->getData('page');
            $this->paginate = ['limit' => 10, 'page' => $page];           
            $totalFlats = $this->AmpFlats->find('all')->count();
            $AmpFlats = $this->paginate($this->AmpFlats)->toArray();
            foreach($AmpFlats as $index=>$flat){
                $AmpFlats[$index]['agreement_date'] = date("jS F, Y", strtotime($flat['agreement_date']));
                $AmpFlats[$index]['created_date'] = date("jS F, Y", strtotime($flat['created_date']));
            }
            $this->httpStatusCode = 200;
            $this->apiResponse['page'] = (int) $page;
            $this->apiResponse['total'] = (int) $totalFlats;
            $this->apiResponse['flats'] = $AmpFlats;
            $this->apiResponse['message'] = "successfully fetched data";
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
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

    public function getsingleflat()
    {
        $id = $this->request->getData('flat_id');
        $AmpFlat = $this->AmpFlats->get($id, [
            'contain' => []
        ]);

        $this->httpStatusCode = 200;
        $this->apiResponse['flat'] = $AmpFlat;
    }

    public function update()
    {
        $id = $this->request->getData('flat_id');
        $AmpFlat = $this->AmpFlats->get($id, [
            'contain' => []
        ]);
        unset($this->request->data['flat_id']);
        $this->request->data['agreement_date'] = $this->customdateformat($this->request->data['agreement_date']); 
        $AmpFlat = $this->AmpFlats->patchEntity($AmpFlat, $this->request->getData());
        if ($this->AmpFlats->save($AmpFlat)) {
            $this->httpStatusCode = 200;
            $this->apiResponse['message'] = 'Flat profile has been updated successfully.';
        }else{
            $this->httpStatusCode = 422;
            $this->apiResponse['message'] = 'Unable to update Flat Profile.';
        }
    }

    public function delete()
    {
        $id = $this->request->getData('flat_id');
        $AmpFlat = $this->AmpFlats->get($id);
        if ($this->AmpFlats->delete($AmpFlat)) {
            $this->httpStatusCode = 200;
            $this->apiResponse['message'] = 'Flat profile has been deleted successfully.';
        } else {
            $this->httpStatusCode = 422;
            $this->apiResponse['message'] = 'Unable to delete Flat Profile.';
        }
    }

    public function getagreementstatus()
    {
        header("Access-Control-Allow-Origin: *");
        $status = array('Expired','Renew','Pending');
        $this->httpStatusCode = 200;
        $this->apiResponse['status'] = $status;
    }

    public function getvacancystatus()
    {
        header("Access-Control-Allow-Origin: *");
        $status = array('Vacant','Partially Occupied','Occupied');
        $this->httpStatusCode = 200;
        $this->apiResponse['status'] = $status;
    }

     /**
     *  Get Cities
     */
    public function getcities()
    {
        header("Access-Control-Allow-Origin: *");
        $options = array();
        $state = $this->request->getData('state');
        $options['conditions']['city_state'] = $state;
        $options['fields'] = array('city_name' => 'DISTINCT city_name');
        $options['order'] = 'city_name';
        $TblCities = TableRegistry::get('CSMap',['table' => 'amp_cities_states_mapping']);
        $cities = $TblCities->find('all',$options)->toArray();
        $tmp_array = array();
        foreach ($cities as $value) {
            $tmp_array[] = trim($value['city_name']);
        }

        $this->httpStatusCode = 200;
        $this->apiResponse['cities'] = $tmp_array;
    }

    public function getstates()
    {
        header("Access-Control-Allow-Origin: *");
        $options = array();
        $options['fields'] = array('city_state' => 'DISTINCT city_state');
        $options['order'] = 'city_state';
        $TblStates = TableRegistry::get('CSMap',['table' => 'amp_cities_states_mapping']);
        $states = $TblStates->find('all',$options)->toArray();
        $tmp_array = array();
        foreach ($states as $value) {
            $tmp_array[] = trim($value['city_state']);
        }

        $this->httpStatusCode = 200;
        $this->apiResponse['states'] = $tmp_array;
    }

}
