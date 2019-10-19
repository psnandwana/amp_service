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

    public function checkpostvariables($data){
        dd($data);
        $error = false;
        foreach($data as $key => $value){
            if ($value=="undefined"){
                $error = true;
                return $error;
            } 
        }
        return $error;   
    }

    public function index()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $page = $this->request->getData('page');
            $limit = 10;
            $start = ($page - 1) * $limit;

            $totalFlats = $this->AmpFlats->find('all')->count();
            $options = array();
            $options['join'] = array(
                array(
                    'table' => 'amp_flat_rooms_mapping',
                    'alias' => 'Rooms',
                    'type' => 'INNER',
                    'conditions' => 'Rooms.flat_id = AmpFlats.id',
                ),
            );

            $options['fields'] = array('id', 'flat_no', 'apartment_name', 'flat_type', 'flat_band', 'agreement_status', 'agreement_date', 'address', 'pincode', 'city', 'state', 'longitude', 'latitude', 'rent_amount', 'maintenance_amount', 'owner_name', 'owner_mobile_no', 'owner_email', 'vacancy_status', 'created_date', 'active_status');

            $options['limit'] = $limit;
            $options['order'] = 'created_date ASC';
            $options['offset'] = $start;

            $AmpFlats = $this->AmpFlats->find('all', $options)->group('AmpFlats.id')->toArray();

            $flatRoomsMapingTable = TableRegistry::get('Room', ['table' => 'amp_flat_rooms_mapping']);
            foreach ($AmpFlats as $index => $flat) {
                $subOptions = array();
                $subOptions['join'] = array(
                    array(
                        'table' => 'amp_room_employee_mapping',
                        'alias' => 'RoomEmpMap',
                        'type' => 'LEFT',
                        'conditions' => ['RoomEmpMap.room_id = Room.id', 'RoomEmpMap.active_status' => '1'],
                    ),
                    array(
                        'table' => 'amp_employees_listing',
                        'alias' => 'Employees',
                        'type' => 'LEFT',
                        'conditions' => 'Employees.id = RoomEmpMap.employee_id',
                    ),
                );
                $subOptions['fields'] = array(
                    'room_id' => 'Room.id',
                    'room_no',
                    'room_band' => 'band',
                    'capacity',
                    'employee__id' => 'Employees.id',
                    'employee__emp_code' => 'Employees.emp_code',
                    'employee__emp_name' => 'Employees.emp_name',
                    'employee__email_id' => 'Employees.email_id',
                    'employee__flat_band' => 'Employees.flat_band',
                );
                $totalRooms = $flatRoomsMapingTable->find('all', $subOptions)->where(['Room.flat_id' => $flat['id']])->toArray();
                $tmp_array = array();
                foreach ($totalRooms as $i => $room) {
                    $tmp_array[$room['room_no']]['room_id'] = $room['room_id'];
                    $tmp_array[$room['room_no']]['room_no'] = $room['room_no'];
                    $tmp_array[$room['room_no']]['room_band'] = (int) $room['room_band'];
                    $tmp_array[$room['room_no']]['capacity'] = $room['capacity'];
                    if ($room['employee']['id'] != null) {
                        $totalRooms[$i]['employee']['id'] = (int) $totalRooms[$i]['employee']['id'];
                        $room['employee']['flat_band'] = (int) $room['employee']['flat_band'];
                        $tmp_array[$room['room_no']]['employees'][] = $room['employee'];
                    } else {
                        $tmp_array[$room['room_no']]['employees'] = array();
                    }
                }
                $rooms = array();
                $band_vacancy = array();
                $vacancy_count = 0;
                foreach ($tmp_array as $key => $room) {
                    $room['room_vacancy'] = $tmp_array[$key]['capacity'] - count($tmp_array[$key]['employees']);
                    $rooms[] = $room;
                    $vacancy_count += $room['room_vacancy'];
                    $band_vacancy[] = array('band' => $room['room_band'], 'vacancy' => $room['room_vacancy']);
                }
                $AmpFlats[$index]['flat_vacancy'] = $band_vacancy;
                $AmpFlats[$index]['vacancy_count'] = $vacancy_count;
                $AmpFlats[$index]['agreement_date'] = date("jS F, Y", strtotime($flat['agreement_date']));
                $AmpFlats[$index]['created_date'] = date("jS F, Y", strtotime($flat['created_date']));
                $AmpFlats[$index]['distance'] = '10 km';
                $AmpFlats[$index]['rooms'] = $rooms;
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
            $rooms = array();
            if (!empty($this->request->data['rooms'])) {
                $rooms = json_decode($this->request->data['rooms']);
            }
            if (count($rooms) > 0) {
                unset($this->request->data['agreement_date']);
                $this->request->data['agreement_date'] = $agreement_date;
                $this->request->data['created_date'] = Time::now();
                $this->request->data['active_status'] = '1';
                $AmpFlat = $this->AmpFlats->patchEntity($AmpFlat, $this->request->getData());
                if ($this->AmpFlats->save($AmpFlat)) {
                    if (count($rooms) > 0) {
                        $roomFlatMapping = TableRegistry::get('amp_flat_rooms_mapping');
                        foreach ($rooms as $room) {
                            $queryInsert = $roomFlatMapping->query();
                            $queryInsert->insert(['flat_id', 'room_no', 'band', 'capacity'])
                                ->values([
                                    'flat_id' => $AmpFlat->id,
                                    'room_no' => $room->room_number,
                                    'band' => $room->band,
                                    'capacity' => $room->capacity,
                                ])
                                ->execute();
                        }
                    }
                    $this->httpStatusCode = 200;
                    $this->apiResponse['message'] = 'Flat Profile has been created successfully';
                } else {
                    $this->httpStatusCode = 422;
                    $this->apiResponse['message'] = 'Unable to create Flat Profile';
                }
            } else {
                $this->httpStatusCode = 422;
                $this->apiResponse['message'] = 'Please enter room details';
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
            if (is_numeric($id)) {
                $page = $this->request->getData('page');
                $options = array();
                $options['conditions']['id'] = $id;
                $options['conditions']['active_status'] = '1';

                $options['fields'] = array('id', 'flat_no', 'apartment_name', 'flat_type', 'flat_band', 'agreement_status', 'agreement_date', 'address', 'pincode', 'city', 'state', 'longitude', 'latitude', 'rent_amount', 'maintenance_amount', 'owner_name', 'owner_mobile_no', 'owner_email', 'vacancy_status', 'created_date', 'active_status');

                $flat = $this->AmpFlats->find('all', $options)->group('AmpFlats.id')->first();
                if (!empty($flat)) {
                    $flat->toArray();
                    $flatRoomsMapingTable = TableRegistry::get('Room', ['table' => 'amp_flat_rooms_mapping']);

                    $subOptions = array();
                    $subOptions['join'] = array(
                        array(
                            'table' => 'amp_room_employee_mapping',
                            'alias' => 'RoomEmpMap',
                            'type' => 'LEFT',
                            'conditions' => ['RoomEmpMap.room_id = Room.id', 'RoomEmpMap.active_status' => '1'],
                        ),
                        array(
                            'table' => 'amp_employees_listing',
                            'alias' => 'Employees',
                            'type' => 'LEFT',
                            'conditions' => 'Employees.id = RoomEmpMap.employee_id',
                        ),
                    );
                    $subOptions['fields'] = array(
                        'room_id' => 'Room.id',
                        'room_no',
                        'room_band' => 'band',
                        'capacity',
                        'employee__id' => 'Employees.id',
                        'employee__emp_code' => 'Employees.emp_code',
                        'employee__emp_name' => 'Employees.emp_name',
                        'employee__email_id' => 'Employees.email_id',
                        'employee__flat_band' => 'Employees.flat_band',
                    );
                    $totalRooms = $flatRoomsMapingTable->find('all', $subOptions)->where(['Room.flat_id' => $flat['id']])->toArray();
                    $tmp_array = array();
                    foreach ($totalRooms as $i => $room) {
                        $tmp_array[$room['room_no']]['room_id'] = $room['room_id'];
                        $tmp_array[$room['room_no']]['room_no'] = $room['room_no'];
                        $tmp_array[$room['room_no']]['room_band'] = (int) $room['room_band'];
                        $tmp_array[$room['room_no']]['capacity'] = $room['capacity'];
                        if ($room['employee']['id'] != null) {
                            $totalRooms[$i]['employee']['id'] = (int) $totalRooms[$i]['employee']['id'];
                            $room['employee']['flat_band'] = (int) $room['employee']['flat_band'];
                            $tmp_array[$room['room_no']]['employees'][] = $room['employee'];

                        } else {
                            $tmp_array[$room['room_no']]['employees'] = array();
                        }
                    }
                    $rooms = array();
                    $band_vacancy = array();
                    $vacancy_count = 0;
                    foreach ($tmp_array as $key => $room) {
                        $room['room_vacancy'] = $tmp_array[$key]['capacity'] - count($tmp_array[$key]['employees']);
                        $rooms[] = $room;
                        $vacancy_count += $room['room_vacancy'];
                        $band_vacancy[] = array('band' => $room['room_band'], 'vacancy' => $room['room_vacancy']);
                    }

                    $flat['flat_vacancy'] = $band_vacancy;
                    $flat['vacancy_count'] = $vacancy_count;
                    $flat['agreement_date'] = date("jS F, Y", strtotime($flat['agreement_date']));
                    $flat['created_date'] = date("jS F, Y", strtotime($flat['created_date']));
                    $flat['distance'] = '10 km';
                    $flat['rooms'] = $rooms;
                    $this->httpStatusCode = 200;
                    $this->apiResponse['flat'] = $flat;
                } else {
                    $this->httpStatusCode = 200;
                    $this->apiResponse['flat'] = null;
                }
            } else {
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
                $rooms = array();
                if (!empty($this->request->data['rooms'])) {
                    $rooms = json_decode($this->request->data['rooms']);
                }
                $AmpFlat = $this->AmpFlats->get($id, [
                    'contain' => [],
                ]);
                unset($this->request->data['flat_id']);
                $agreement_date = $this->customdateformat($this->request->data['agreement_date']);
                unset($this->request->data['agreement_date']);
                $this->request->data['agreement_date'] = $agreement_date;
                $AmpFlat = $this->AmpFlats->patchEntity($AmpFlat, $this->request->getData());
                if ($this->AmpFlats->save($AmpFlat)) {
                    if (count($rooms) > 0) {
                        $roomFlatMapping = TableRegistry::get('amp_flat_rooms_mapping');
                        foreach ($rooms as $room) {
                            $queryUpdate = $roomFlatMapping->query();
                            $queryUpdate->update()
                                ->set([
                                    'flat_id' => $AmpFlat->id,
                                    'room_no' => $room->room_number,
                                    'band' => $room->band,
                                    'capacity' => $room->capacity,
                                ])
                                ->where(['id' => $room->room_id])
                                ->execute();
                        }
                    }
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

    public function deactivate()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            try {
                dd($this->request->getData());
                // dd($this->checkpostvariables($this->request->getData));
                $id = $this->request->getData('flat_id');
                $AmpFlat = $this->AmpFlats->get($id);
                $AmpFlat->active_status = '0';
                if ($this->AmpFlats->save($AmpFlat)) {
                    $this->httpStatusCode = 200;
                    $this->apiResponse['message'] = 'Flat profile has been deactivated successfully.';
                } else {
                    $this->httpStatusCode = 422;
                    $this->apiResponse['message'] = 'Unable to deactivate Flat Profile.';
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
        $band = array('5500', '8500', '12500', '15500');
        $this->httpStatusCode = 200;
        $this->apiResponse['band'] = $band;
    }

    public function getflattype()
    {
        header("Access-Control-Allow-Origin: *");
        $type[] = array('value' => '1', 'name' => '1 BHK');
        $type[] = array('value' => '2', 'name' => '2 BHK');
        $type[] = array('value' => '3', 'name' => '3 BHK');
        $type[] = array('value' => '4', 'name' => '4 BHK');
        $type[] = array('value' => '5', 'name' => '5 BHK');
        $this->httpStatusCode = 200;
        $this->apiResponse['types'] = $type;
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
            $flatEmpMappingTable = TableRegistry::get('amp_flat_employees_mapping');
            $flatRoomMappingTable = TableRegistry::get('amp_flat_rooms_mapping');
            $roomEmployeeMappingTable = TableRegistry::get('amp_room_employee_mapping');
            $flatsTable = TableRegistry::get('amp_flats');
            $empID = $this->request->data('employee_id');
            $flatID = $this->request->data('flat_id');
            $roomID = $this->request->data('room_id');
            $roomCapapcity = $flatRoomMappingTable->find('all')->where(['id' => $roomID])->toList();
            $employeeCount = $roomEmployeeMappingTable->find('all')->where(['room_id' => $roomID])->toList();
            $capacity = 0;
            if (count($roomCapapcity) > 0) {
                $capacity = $roomCapapcity[0]['capacity'];
            }
            $empcount = 0;
            if (count($employeeCount) > 0) {
                $empcount = count($employeeCount);
            }
            date_default_timezone_set('Asia/Kolkata');
            $current_date = date('Y-m-d H:i:s');
            if (($capacity - $empcount) > 0) {
                $queryInsert = $roomEmployeeMappingTable->query();
                $queryInsert->insert(['room_id', 'flat_id', 'employee_id', 'assigned_by', 'assigned_date'])
                    ->values([
                        'room_id' => $roomID,
                        'flat_id' => $flatID,
                        'employee_id' => $empID,
                        'assigned_by' => 1000,
                        'assigned_date' => $current_date,
                    ])->execute();
                $flatCapacityTable = $flatRoomMappingTable->find('all')->where(['flat_id' => $flatID])->toList();
                $flatcapacity = 0;
                foreach ($flatCapacityTable as $room) {
                    $flatcapacity += $room['capacity'];
                }
                $flatoccupancycount = $roomEmployeeMappingTable->find('all')->where(['flat_id' => $flatID])->count();
                if ($flatoccupancycount == 0) {
                    $queryUpdate = $flatsTable->query();
                    $queryUpdate->update()
                        ->set([
                            'vacancy_status' => 'Vacant',
                        ])
                        ->where(['id' => $flatID])
                        ->execute();
                } elseif ($flatoccupancycount == $flatcapacity) {
                    $queryUpdate = $flatsTable->query();
                    $queryUpdate->update()
                        ->set([
                            'vacancy_status' => 'Fully Occupied',
                        ])
                        ->where(['id' => $flatID])
                        ->execute();
                } else {
                    echo "Partially Occupied";
                    $queryUpdate = $flatsTable->query();
                    $queryUpdate->update()
                        ->set([
                            'vacancy_status' => 'Partially Occupied',
                        ])
                        ->where(['id' => $flatID])
                        ->execute();
                }
                $this->httpStatusCode = 200;
                $this->apiResponse['message'] = 'Flat has been assigned successfully.';
            } else {
                $this->httpStatusCode = 422;
                $this->apiResponse['message'] = 'Flat already Occupied';
            }

        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expaired";
        }
    }

    public function getkpi()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $data = $this->request->data;
            $flatsTable = TableRegistry::get('amp_flats');
            $employessTable = TableRegistry::get('amp_room_employee_mapping');
            /* Conditions */
            // $condition1 = array();
            // $condition2 = array();
            // $condition1['active_status'] = '1';
            // $condition2['active_status'] = '1';
            // /*  */
            // $flatsCount = $flatsTable->find('all')->Where($condition1)->count();
            // $employeesCount = $employessTable->find('all')->where($condition1)->count();
            $kpi = array();
            $kpi['flatscount'] = 10;
            $kpi['occupied'] = 6;
            $kpi['vacant'] = 4;
            $kpi['employees'] = 18;
            $this->httpStatusCode = 200;
            $this->apiResponse['kpis'] = $kpi;

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
            $queryInsert->insert(['flat_id', 'rent_month', 'rent_year', 'rent_amount', 'payment_date', 'payment_by'])
                ->values([
                    'flat_id' => $flatID,
                    'rent_month' => $rent_month,
                    'rent_year' => $rent_year,
                    'rent_amount' => $rent_amount,
                    'payment_date' => Time::now(),
                    'payment_by' => 1000,
                ])->execute();

            $this->httpStatusCode = 200;
            $this->apiResponse['message'] = 'Rent has been paid successfully';
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }
}
