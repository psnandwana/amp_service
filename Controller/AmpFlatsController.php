<?php
namespace App\Controller;

use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use RestApi\Controller\ApiController;

class AmpFlatsController extends ApiController
{

    public function customdateformat($chkdt)
    {
        $chkdt = trim($chkdt);
        $month = substr($chkdt, 4, 3);
        if ($month == 'Jan') {
            $month = '01';
        } else if ($month == 'Feb') {
            $month = '02';
        } else if ($month == 'Mar') {
            $month = '03';
        } else if ($month == 'Apr') {
            $month = '04';
        } else if ($month == 'May') {
            $month = '05';
        } else if ($month == 'Jun') {
            $month = '06';
        } else if ($month == 'Jul') {
            $month = '07';
        } else if ($month == 'Aug') {
            $month = '08';
        } else if ($month == 'Sep') {
            $month = '09';
        } else if ($month == 'Oct') {
            $month = '10';
        } else if ($month == 'Nov') {
            $month = '11';
        } else if ($month == 'Dec') {
            $month = '12';
        } else {
            return $chkdt;
        }

        $date = substr($chkdt, 7, 3);
        $year = substr($chkdt, 10, 5);
       
        return date("Y-m-d", mktime(0, 0, 0, $month, $date, $year));
    }

    public function index()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {           
            $page = $this->request->getData('page');
            $this->paginate = ['limit' => 10, 'page' => $page];
            $totalFlats = $this->AmpFlats->find('all')->count();
            $this->paginate['contain'] = ['AmpEmployeesListing'];
            $AmpFlats = $this->paginate($this->AmpFlats)->toArray();
            foreach ($AmpFlats as $index => $flat) {
                if(count($flat['amp_employees_listing']) > 0){
                    $band5500 = 0;
                    $flatVacancy = 0;
                    foreach($flat['amp_employees_listing'] as $flatEmp){
                        unset($flatEmp['_joinData']);
                        if($flatEmp['flat_band'] == '5500'){
                            $band5500 += 1;
                        }
                    }
                    if($band5500 != 0){
                        $flatVacancy += 1;
                    }
                    $flatVacancy += $flat['flat_capacity'] - count($flat['amp_employees_listing']);
                    $AmpFlats[$index]['vacancy_number'] = $flatVacancy;
                }else{
                    $AmpFlats[$index]['vacancy_number'] = $flat['flat_capacity'];
                }
                $AmpFlats[$index]['agreement_date'] = date("jS F, Y", strtotime($flat['agreement_date']));
                $AmpFlats[$index]['created_date'] = date("jS F, Y", strtotime($flat['created_date']));
                $AmpFlats[$index]['distance'] = '10 km';
                $AmpFlats[$index]['employees'] = $flat['amp_employees_listing'];
                unset($flat['amp_employees_listing']);                
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
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $AmpFlat = $this->AmpFlats->newEntity();
            $agreement_date = $this->customdateformat($this->request->data['agreement_date']);
           
            unset($this->request->data['agreement_date']);
            $this->request->data['agreement_date'] = $agreement_date;
            $this->request->data['created_date'] = Time::now();
            $AmpFlat = $this->AmpFlats->patchEntity($AmpFlat, $this->request->getData());

            if ($this->AmpFlats->save($AmpFlat)) {
                $this->httpStatusCode = 200;
                $this->apiResponse['message'] = 'Flat profile has been created successfully.';
            } else {
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
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $id = $this->request->getData('flat_id');            
            if (is_numeric($id)){
                try {
                    $AmpFlat = $this->AmpFlats->get($id, [
                        'contain' => ['AmpEmployeesListing'],
                    ])->toArray();
    
                    $flatVacancy = 0;
                    if(count($AmpFlat['amp_employees_listing']) > 0){
                        
                        $band5500 = 0;                    
                        foreach($AmpFlat['amp_employees_listing'] as $index=>$flatEmp){
                            unset($AmpFlat['amp_employees_listing'][$index]['_joinData']);
                            if($flatEmp['flat_band'] == '5500'){
                                $band5500 += 1;
                            }
                        }
                        if($band5500 != 0){
                            $flatVacancy += 1;
                        }
                       
                        $flatVacancy += $AmpFlat['flat_capacity'] - count($AmpFlat['amp_employees_listing']);
                    }else{
                        $flatVacancy = $AmpFlat['flat_capacity'];
                    }
                    $AmpFlat['vacancy_number'] = $flatVacancy;
                    $AmpFlat['distance'] = '10 km';
                    $AmpFlat['employees'] = $AmpFlat['amp_employees_listing'];
                    unset($AmpFlat['amp_employees_listing']);  
                    $this->httpStatusCode = 200;
                    $this->apiResponse['flat'] = $AmpFlat;
                } catch (\Cake\Datasource\Exception\RecordNotFoundException $exeption) {
                    $this->httpStatusCode = 200;
                    $this->apiResponse['flat'] = null;
                }
            }else{
                $this->httpStatusCode = 200;
                $this->apiResponse['flat'] = null;
            }            
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function update()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            try {
                $id = $this->request->getData('flat_id');
                $AmpFlat = $this->AmpFlats->get($id, [
                    'contain' => [],
                ]);
                unset($this->request->data['flat_id']);
                $agreement_date = $this->customdateformat($this->request->data['agreement_date']);
                unset($this->request->data['agreement_date']);
                $this->request->data['agreement_date'] = $agreement_date;
                $AmpFlat = $this->AmpFlats->patchEntity($AmpFlat, $this->request->getData());
                if ($this->AmpFlats->save($AmpFlat)) {
                    $this->httpStatusCode = 200;
                    $this->apiResponse['message'] = 'Flat profile has been updated successfully.';
                } else {
                    $this->httpStatusCode = 422;
                    $this->apiResponse['message'] = 'Unable to update Flat Profile.';
                }
            } catch (\Cake\Datasource\Exception\RecordNotFoundException $exeption) {
                $this->httpStatusCode = 422;
                $this->apiResponse['message'] = "Selected record not found";
            }            
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function delete()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            try {
                $id = $this->request->getData('flat_id');
                $AmpFlat = $this->AmpFlats->get($id);
                if ($this->AmpFlats->delete($AmpFlat)) {
                    $this->httpStatusCode = 200;
                    $this->apiResponse['message'] = 'Flat profile has been deleted successfully.';
                } else {
                    $this->httpStatusCode = 422;
                    $this->apiResponse['message'] = 'Unable to delete Flat Profile.';
                }
            } catch (\Cake\Datasource\Exception\RecordNotFoundException $exeption) {
                $this->httpStatusCode = 422;
                $this->apiResponse['message'] = "Selected record not found";
            }
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function getagreementstatus()
    {
        header("Access-Control-Allow-Origin: *");
        $status = array('Renewed', 'Pending');
        $this->httpStatusCode = 200;
        $this->apiResponse['status'] = $status;
    }

    public function getvacancystatus()
    {
        header("Access-Control-Allow-Origin: *");
        $status = array('Vacant', 'Partially Occupied', 'Fully Occupied');
        $this->httpStatusCode = 200;
        $this->apiResponse['status'] = $status;
    }

    public function getflatband()
    {
        header("Access-Control-Allow-Origin: *");
        $band = array('8500', '12500', '15500');
        $this->httpStatusCode = 200;
        $this->apiResponse['band'] = $band;
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
        $TblCities = TableRegistry::get('CSMap', ['table' => 'amp_cities_states_mapping']);
        $cities = $TblCities->find('all', $options)->toArray();
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
        $TblStates = TableRegistry::get('CSMap', ['table' => 'amp_cities_states_mapping']);
        $states = $TblStates->find('all', $options)->toArray();
        $tmp_array = array();
        foreach ($states as $value) {
            $tmp_array[] = trim($value['city_state']);
        }

        $this->httpStatusCode = 200;
        $this->apiResponse['states'] = $tmp_array;
    }

    public function assignflat()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            try {
                $flatEmpMappingTable = TableRegistry::get('amp_flat_employees_mapping');
                $empID = $this->request->getData('employee_id');
                $flatID = $this->request->getData('flat_id');
                $AmpFlat = $this->AmpFlats->get($flatID, [
                    'contain' => ['AmpEmployeesListing'],
                ])->toArray();

                $flatVacancy = 0;
                if(count($AmpFlat['amp_employees_listing']) > 0){
                    $band5500 = 0;                    
                    foreach($AmpFlat['amp_employees_listing'] as $flatEmp){
                        if($flatEmp['flat_band'] == '5500'){
                            $band5500 += 1;
                        }
                    }
                    if($band5500 != 0){
                        $flatVacancy += 1;
                    }
                   
                    $flatVacancy += $AmpFlat['flat_capacity'] - count($AmpFlat['amp_employees_listing']);
                }else{
                    $flatVacancy = $AmpFlat['flat_capacity'];
                }
                if($flatVacancy != 0 && $AmpFlat['vacancy_status'] != 'Occupied'){
                    $checkAlreadyAssigned = $flatEmpMappingTable->find('all')->where(['employee_id' => $empID])->toArray();
                    if (count($checkAlreadyAssigned) > 0) {
                        $this->httpStatusCode = 422;
                        $this->apiResponse['message'] = 'Flat is already assigned to selected Employee';
                    } else {
                        $queryInsert = $flatEmpMappingTable->query();
                        $queryInsert->insert(['flat_id', 'employee_id', 'assigned_by', 'assigned_date'])
                            ->values([
                                'flat_id' => $flatID,
                                'employee_id' => $empID,
                                'assigned_by' => 1000,
                                'assigned_date' => Time::now(),
                            ])
                            ->execute();
                        $this->httpStatusCode = 200;
                        $this->apiResponse['message'] = 'Flat has been assigned successfully.'; 
                    }                    
                }else{
                    $this->httpStatusCode = 422;
                    $this->apiResponse['message'] = 'Flat already Occupied';
                }
            } catch (\Cake\Datasource\Exception\RecordNotFoundException $exeption) {
                $this->httpStatusCode = 422;
                $this->apiResponse['message'] = 'Selected flat not found';
            }
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function rentpayment()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $flatRentTable = TableRegistry::get('amp_flat_rent');
            $flatID = $this->request->getData('flat_id');
            $rent_month = $this->request->getData('rent_month');
            $rent_year = $this->request->getData('rent_year');
            $rent_amount = $this->request->getData('rent_amount');
                     
            $queryInsert = $flatRentTable->query();
            $queryInsert->insert(['flat_id','rent_month','rent_year','rent_amount','payment_date','payment_by'])
                ->values([
                    'flat_id' => $flatID,
                    'rent_month' => $rent_month,
                    'rent_year' => $rent_year,
                    'rent_amount' => $rent_amount,                   
                    'payment_date' => Time::now(),
                    'payment_by' => 1000
                ])->execute();

            $this->httpStatusCode = 200;
            $this->apiResponse['message'] = 'Rent has been paid successfully';
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }
}
