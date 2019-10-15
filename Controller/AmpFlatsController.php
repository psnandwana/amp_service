<?php
namespace App\Controller;

use Cake\I18n\Time;
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
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $data = $this->request->data;

            $flat_no = $data['flat_no'];
            $apartment_name = $data['apartment_name'];
            $flat_type = $data['flat_type'];
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

            $queryInsert = $this->AmpFlat->query();
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
            $this->apiResponse['message'] = 'flat details has been created successfully.';
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }
}
