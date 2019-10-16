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

    public function newflat()
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

    public function create()
    {
        // if ($this->checkToken()) {
        $data = $this->request->data;
        $flat_no = $data['flat_no'];
        $apartment_name = $data['apartment_name'];
        $flat_type = $data['flat_type'];
        if ($flat_type!=""){
            $flat_type = (int)$flat_type;
        }
        $agreement_status = $data['agreement_status'];
        $agreement_date = $data['agreement_date'];
        $address = $data['address'];
        $pincode = $data['pincode'];
        $city = $data['city'];
        $state = $data['state'];
        $longitude = $data['longitude'];
        $latitude = $data['latitude'];
        $rent_amount = $data['rent_amount'];
        $maintenance_amount = $data['maintenance_amount'];
        $owner_name = $data['owner_name'];
        $owner_mobile_no = $data['owner_mobile_no'];
        $owner_email = $data['owner_email'];
        $vacancy_status = $data['vacancy_status'];
        $flat_capacity = $data['flat_capacity'];
        $flat_band = $data['flat_band'];

        $queryInsert = $this->AmpFlats->query();
        $queryInsert->insert(['flat_no', 'apartment_name', 'flat_type', 'agreement_status', 'agreement_date', 'address', 'pincode', 'city', 'state', 'longitude', 'latitude', 'rent_amount', 'maintenance_amount', 'owner_name', 'owner_mobile_no', 'owner_email', 'vacancy_status', 'flat_capacity', 'flat_band', 'created_date'])
            ->values([
                'flat_no' => $flat_no,
                'apartment_name' => $apartment_name,
                'flat_type' => $flat_type,
                'agreement_status' => $agreement_status,
                'agreement_date' => $agreement_date,
                'address' => $address,
                'pincode' => $pincode,
                'city' => $city,
                'state' => $state,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'rent_amount' => $rent_amount,
                'maintenance_amount' => $maintenance_amount,
                'owner_name' => $owner_name,
                'owner_mobile_no' => $owner_mobile_no,
                'owner_email' => $owner_email,
                'vacancy_status' => $vacancy_status,
                'flat_capacity' => $flat_capacity,
                'flat_band' => $flat_band,
                'created_date' => Time::now(),
            ])
            ->execute();
        $this->httpStatusCode = 200;
        $this->apiResponse['message'] = 'flat profile has been  successfully.';
        // } else {
        //     $this->httpStatusCode = 403;
        //     $this->apiResponse['message'] = "your session has been expired";
        // }
    }
}
