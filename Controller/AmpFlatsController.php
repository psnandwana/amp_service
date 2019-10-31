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

    public function checkpostvariables($data)
    {
        $error = false;
        foreach ($data as $key => $value) {
            if ($value == "undefined") {
                $error = true;
                return array("error_string" => "$key" . " has value undefined", "error_status" => $error);
            }
        }
        return array("error_string" => "Success", "error_status" => $error);
    }

    public function index()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $page = $this->request->getData('page');
            $limit = 10;
            $start = ($page - 1) * $limit;

            
            $options = array();

            if ($this->request->getData('flat_type') != "") {
                $flat_type = $this->request->getData('flat_type');
                $flat_type = json_decode($flat_type);
                // if (count($flat_type)>1){
                //     $options['conditions']['flat_type'] = $flat_type[0];
                // }else{
                $temp = array();
                foreach($flat_type as $type){
                    $temp['flat_type'] = $type;
                }
                $options['conditions']['or'] = $temp;
                //}
                
            }

            if ($this->request->getData('flat_band') != "") {
                $options['conditions']['flat_band'] = $this->request->getData('flat_band');
            }

            if ($this->request->getData('vacancy_status') != "") {
                $options['conditions']['vacancy_status'] = $this->request->getData('vacancy_status');
            }

            if ($this->request->getData('agreement_status') != "") {
                $options['conditions']['agreement_status'] = $this->request->getData('agreement_status');
            }

            if ($this->request->getData('city') != "") {
                $options['conditions']['city'] = $this->request->getData('city');
            }

            if ($this->request->getData('state') != "") {
                $options['conditions']['state'] = $this->request->getData('state');
            }

            if ($this->request->getData('active_status') != "") {
                $options['conditions']['active_status'] = $this->request->getData('active_status');
            }
            $totalFlats = $this->AmpFlats->find('all',$options)->count();

            $options['fields'] = array('id', 'flat_no', 'apartment_name', 'flat_type', 'flat_band', 'agreement_status', 'agreement_date', 'address', 'pincode', 'city', 'state', 'google_map_link', 'rent_amount', 'maintenance_amount', 'owner_name', 'owner_mobile_no', 'owner_email', 'vacancy_status', 'created_date', 'active_status');

            $options['join'] = array(
                array(
                    'table' => 'amp_flat_rooms_mapping',
                    'alias' => 'Rooms',
                    'type' => 'INNER',
                    'conditions' => 'Rooms.flat_id = AmpFlats.id',
                )
            );

            $options['limit'] = $limit;
            $options['order'] = 'created_date DESC';
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
                $subOptions['order'] = 'Room.id ASC';
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
                    $band_vacancy[] = array('band' => (int) $room['room_band'], 'vacancy' => $room['room_vacancy']);
                }
                $AmpFlats[$index]['flat_vacancy'] = $band_vacancy;
                $AmpFlats[$index]['vacancy_count'] = $vacancy_count;
                $AmpFlats[$index]['rent_amount'] = moneyFormatIndia((int) $AmpFlats[$index]['rent_amount']);
                $AmpFlats[$index]['maintenance_amount'] = moneyFormatIndia((int) $AmpFlats[$index]['maintenance_amount']);
                if ($AmpFlats[$index]['agreement_status'] == 'Pending') {
                    $AmpFlats[$index]['agreement_date'] = '';
                } else {
                    $AmpFlats[$index]['agreement_date'] = date("jS F, Y", strtotime($flat['agreement_date']));
                }
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
            date_default_timezone_set('Asia/Kolkata');
            $current_date = date('Y-m-d H:i:s');
            $AmpFlat = $this->AmpFlats->newEntity();
            $agreement_date = $this->customdateformat($this->request->data['agreement_date']);
            $rooms = array();
            if (!empty($this->request->data['rooms'])) {
                $rooms = json_decode($this->request->data['rooms']);
            }
            if (count($rooms) > 0) {
                unset($this->request->data['agreement_date']);
                if ($this->request->data['agreement_status'] != 'Pending') {
                    $this->request->data['agreement_date'] = $agreement_date;
                }
                $this->request->data['created_date'] = $current_date;
                $this->request->data['active_status'] = '1';
                $AmpFlat = $this->AmpFlats->patchEntity($AmpFlat, $this->request->getData());
                if ($this->AmpFlats->save($AmpFlat)) {
                    if (count($rooms) > 0) {
                        $roomFlatMapping = TableRegistry::get('amp_flat_rooms_mapping');
                        foreach ($rooms as $room) {
                            $queryInsert = $roomFlatMapping->query();
                            $queryInsert->insert(['room_no', 'band', 'capacity', 'flat_id'])
                                ->values([
                                    'room_no' => $room->room_no,
                                    'band' => $room->flat_band,
                                    'capacity' => $room->room_capacity,
                                    'flat_id' => $AmpFlat->id,
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
                $options['fields'] = array('id', 'flat_no', 'apartment_name', 'flat_type', 'flat_band', 'agreement_status', 'agreement_date', 'address', 'pincode', 'city', 'state', 'google_map_link', 'rent_amount', 'maintenance_amount', 'owner_name', 'owner_mobile_no', 'owner_email', 'vacancy_status', 'created_date', 'active_status');

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
                    $subOptions['order'] = 'Room.id ASC';
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
                        $band_vacancy[] = array('band' => (int) $room['room_band'], 'vacancy' => $room['room_vacancy']);
                    }

                    $flat['flat_vacancy'] = $band_vacancy;
                    $flat['vacancy_count'] = $vacancy_count;
                    $flat['rent_amount'] = moneyFormatIndia((int) $flat['rent_amount']);
                    $flat['flat_band'] = (int) $flat['flat_band'];
                    $flat['maintenance_amount'] = moneyFormatIndia((int) $flat['maintenance_amount']);
                    if ($flat['agreement_status'] == 'Pending') {
                        $flat['agreement_date'] = '';
                    } else {
                        $flat['agreement_date'] = date("Y-m-d", strtotime($flat['agreement_date']));
                    }
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
                if ($this->request->data['flat_id'] == null) {
                    $this->httpStatusCode = 422;
                    $this->apiResponse['message'] = 'Flat ID is required';
                } else {
                    $id = $this->request->getData('flat_id');
                    // $rooms = array();
                    // if (!empty($this->request->data['rooms'])) {
                    //     $rooms = json_decode($this->request->data['rooms']);
                    // }
                    $AmpFlat = $this->AmpFlats->get($id, [
                        'contain' => [],
                    ]);
                    unset($this->request->data['flat_id']);
                    $agreement_date = $this->customdateformat($this->request->data['agreement_date']);
                    unset($this->request->data['agreement_date']);
                    if ($this->request->data['agreement_status'] != 'Pending') {
                        $this->request->data['agreement_date'] = $agreement_date;
                    }
                    $AmpFlat = $this->AmpFlats->patchEntity($AmpFlat, $this->request->getData());
                    if ($this->AmpFlats->save($AmpFlat)) {
                        // if (count($rooms) > 0) {
                        //     $roomFlatMapping = TableRegistry::get('amp_flat_rooms_mapping');
                        //     foreach ($rooms as $room) {
                        //         $queryUpdate = $roomFlatMapping->query();
                        //         $queryUpdate->update()
                        //             ->set([
                        //                 'flat_id' => $AmpFlat->id,
                        //                 'room_no' => $room->room_no,
                        //                 'band' => $room->flat_band,
                        //                 'capacity' => $room->room_capacity,
                        //             ])
                        //             ->where(['id' => $room->room_id])
                        //             ->execute();
                        //     }
                        // }
                        $this->httpStatusCode = 200;
                        $this->apiResponse['message'] = 'Flat profile has been updated successfully.';
                    } else {
                        $this->httpStatusCode = 422;
                        $this->apiResponse['message'] = 'Unable to update Flat Profile.';
                    }
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
                $var = $this->checkpostvariables($this->request->getData());
                if (!($var['error_status'])) {
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
                } else {
                    $this->httpStatusCode = 422;
                    $this->apiResponse['message'] = $var['error_string'];
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

    public function activate()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            try {
                $var = $this->checkpostvariables($this->request->getData());
                if (!($var['error_status'])) {
                    $id = $this->request->getData('flat_id');
                    $AmpFlat = $this->AmpFlats->get($id);
                    $AmpFlat->active_status = '1';
                    if ($this->AmpFlats->save($AmpFlat)) {
                        $this->httpStatusCode = 200;
                        $this->apiResponse['message'] = 'Flat profile has been activated successfully.';
                    } else {
                        $this->httpStatusCode = 422;
                        $this->apiResponse['message'] = 'Unable to deactivate Flat Profile.';
                    }
                } else {
                    $this->httpStatusCode = 422;
                    $this->apiResponse['message'] = $var['error_string'];
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
        $band = array(5500, 8500, 12500, 15500);
        $this->httpStatusCode = 200;
        $this->apiResponse['band'] = $band;
    }

    public function getflattype()
    {
        header("Access-Control-Allow-Origin: *");
        $type[] = array('value' => 1, 'name' => '1 BHK');
        $type[] = array('value' => 2, 'name' => '2 BHK');
        $type[] = array('value' => 3, 'name' => '3 BHK');
        $type[] = array('value' => 4, 'name' => '4 BHK');
        $type[] = array('value' => 5, 'name' => '5 BHK');
        $this->httpStatusCode = 200;
        $this->apiResponse['types'] = $type;
    }

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
            $empID = $this->request->data('employee_id');
            $flatID = $this->request->data('flat_id');
            $roomID = $this->request->data('room_id');
            date_default_timezone_set('Asia/Kolkata');
            $current_date = date('Y-m-d H:i:s');

            $flatRoomMappingTable = TableRegistry::get('amp_flat_rooms_mapping');
            $roomEmployeeMappingTable = TableRegistry::get('amp_room_employee_mapping');
            $flatsTable = TableRegistry::get('amp_flats');

            $roomCapacity = $flatRoomMappingTable->find('all')->where(['id' => $roomID])->first()->toArray();
            $employeeCount = $roomEmployeeMappingTable->find('all')->where(['room_id' => $roomID, 'flat_id' => $flatID, 'active_status' => '1'])->count();
            $roomCapacity = $roomCapacity['capacity'];
            $empCount = $employeeCount;
            $totalVanancy = $roomCapacity - $empCount;
            if ($totalVanancy > 0) {
                $queryInsert = $roomEmployeeMappingTable->query();
                $queryInsert->insert(['room_id', 'flat_id', 'employee_id', 'assigned_by', 'assigned_date'])
                    ->values([
                        'room_id' => $roomID,
                        'flat_id' => $flatID,
                        'employee_id' => $empID,
                        'assigned_by' => 1000,
                        'assigned_date' => $current_date,
                    ])->execute();
                $roomTable = $flatRoomMappingTable->find('all')->where(['flat_id' => $flatID])->toList();
                $flatCapacity = 0;
                foreach ($roomTable as $room) {
                    $flatCapacity += $room['capacity'];
                }
                $totalFlatOccupancy = $roomEmployeeMappingTable->find('all')->where(['flat_id' => $flatID, 'active_status' => '1'])->count();
                if ($totalFlatOccupancy == 0) {
                    $vacancyStatus = 'Vacant';
                } elseif ($totalFlatOccupancy == $flatCapacity) {
                    $vacancyStatus = 'Fully Occupied';
                } else {
                    $vacancyStatus = 'Partially Occupied';
                }
                $queryUpdate = $flatsTable->query();
                $queryUpdate->update()
                    ->set(['vacancy_status' => $vacancyStatus])
                    ->where(['id' => $flatID])
                    ->execute();
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

    public function unassignflat()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $flatRoomMappingTable = TableRegistry::get('amp_flat_rooms_mapping');
            $roomEmployeeMappingTable = TableRegistry::get('amp_room_employee_mapping');
            $flatsTable = TableRegistry::get('amp_flats');

            $empID = $this->request->data('employee_id');
            $flatID = $this->request->data('flat_id');
            $roomID = $this->request->data('room_id');
            date_default_timezone_set('Asia/Kolkata');
            $current_date = date('Y-m-d H:i:s');

            $queryRoomUpdate = $roomEmployeeMappingTable->query();
            $queryRoomUpdate->update()
                ->set(['active_status' => '0', 'unassigned_date' => $current_date])
                ->where(['employee_id' => $empID, 'flat_id' => $flatID, 'room_id' => $roomID])
                ->execute();

            $roomTable = $flatRoomMappingTable->find('all')->where(['flat_id' => $flatID])->toList();
            $flatCapacity = 0;
            foreach ($roomTable as $room) {
                $flatCapacity += $room['capacity'];
            }
            $totalFlatOccupancy = $roomEmployeeMappingTable->find('all')->where(['flat_id' => $flatID, 'active_status' => '1'])->count();
            if ($totalFlatOccupancy == 0) {
                $vacancyStatus = 'Vacant';
            } elseif ($totalFlatOccupancy == $flatCapacity) {
                $vacancyStatus = 'Fully Occupied';
            } else {
                $vacancyStatus = 'Partially Occupied';
            }
            $queryUpdate = $flatsTable->query();
            $queryUpdate->update()
                ->set(['vacancy_status' => $vacancyStatus])
                ->where(['id' => $flatID])
                ->execute();
            $this->httpStatusCode = 200;
            $this->apiResponse['message'] = 'Flat has been unassigned successfully.';
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expaired";
        }
    }

    public function getkeypoint()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $flatsTable = TableRegistry::get('amp_flats');
            $employessTable = TableRegistry::get('amp_room_employee_mapping');
            $options = array();
            if ($this->request->getData('flat_type') != "") {
                $flat_type = $this->request->getData('flat_type');
                $flat_type = json_decode($flat_type);
                $temp = array();
                foreach($flat_type as $type){
                    $temp['flat_type'] = $type;
                }
                $options['conditions']['or'] = $temp;
                //$options['conditions']['flat_type']l = $this->request->getData('flat_type');
            }

            if ($this->request->getData('flat_band') != "") {
                $options['conditions']['flat_band'] = $this->request->getData('flat_band');
            }

            if ($this->request->getData('agreement_status') != "") {
                $options['conditions']['agreement_status'] = $this->request->getData('agreement_status');
            }

            if ($this->request->getData('city') != "") {
                $options['conditions']['city'] = $this->request->getData('city');
            }

            if ($this->request->getData('state') != "") {
                $options['conditions']['state'] = $this->request->getData('state');
            }

            if ($this->request->getData('active_status') != "") {
                $options['conditions']['active_status'] = $this->request->getData('active_status');
            }

            $totalflats = $this->AmpFlats->find('all', $options)->count();
            $options['conditions']['vacancy_status'] = 'Vacant';
            $vacantflats = $this->AmpFlats->find('all', $options)->count();
            $options['conditions']['vacancy_status'] = 'Occupied';
            $occupiedflats = $this->AmpFlats->find('all', $options)->count();
            $options['conditions']['vacancy_status'] = 'Partially Occupied';
            $partiallyflats = $this->AmpFlats->find('all', $options)->count();
            $allowanceOption['conditions']['vacancy_status'] = 'Vacant';
            $allowanceOption['fields'] = array(
                'total_allowance' => 'SUM(rent_amount)'
            );
            $vacantAmount = 0;
            $vacantAllowance = $this->AmpFlats->find('all', $allowanceOption)->first();  
            if($vacantAllowance->total_allowance != null){
                $vacantAmount = $vacantAllowance->total_allowance;
            }         
            $data[] = array('name' => 'Total Flats','value' => $totalflats);
            $data[] = array('name' => 'Vacant Flats','value' => $vacantflats);
            $data[] = array('name' => 'Occupied Flats','value' => $occupiedflats);
            $data[] = array('name' => 'Partially Occupied Flats','value' => $partiallyflats);
            $data[] = array('name' => 'Vacant Allowance','value' => moneyFormatIndia($vacantAmount));
            
            $this->httpStatusCode = 200;
            $this->apiResponse['data'] = $data;

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

    public function getvacantrom()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $id = $this->request->getData('flat_id');
            if (is_numeric($id)) {
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
                $totalRooms = $flatRoomsMapingTable->find('all', $subOptions)->where(['Room.flat_id' => $id])->toArray();
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
                foreach ($tmp_array as $key => $room) {
                    $room_vacancy = $tmp_array[$key]['capacity'] - count($tmp_array[$key]['employees']);
                    if ($room_vacancy > 0) {
                        unset($room['employees']);
                        $rooms[] = $room;
                    }
                }

                $this->httpStatusCode = 200;
                $this->apiResponse['rooms'] = $rooms;

            } else {
                $this->httpStatusCode = 200;
                $this->apiResponse['flat'] = null;
            }
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function uploadflats()
    {
        $data = $this->request->data['upload_flats'];
        date_default_timezone_set('Asia/Kolkata');
        $current_date = date('Y-m-d H:i:s');
        if (isset($data['name'])) {
            $file = $data['tmp_name'];
            $handle = fopen($file, "r");
            $headerLine = true;
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                if ($headerLine) {$headerLine = false;} else {
                    $AmpFlat = $this->AmpFlats->newEntity();
                    $param = array();
                    $param["flat_no"] = $row[0];
                    $param["apartment_name"] = $row[1];
                    $param["flat_type"] = chop($row[2], " BHK");
                    $param["flat_band"] = $row[3];
                    $param["agreement_status"] = $row[4];
                    if ($param["agreement_status"] == 'Pending') {
                        $param["agreement_date"] = '';
                    } else {
                        $param["agreement_date"] = $row[5];
                    }
                    $param["address"] = $row[6];
                    $param["pincode"] = $row[7];
                    $param["city"] = $row[8];
                    $param["state"] = $row[9];
                    $param["google_map_link"] = $row[10];
                    $param["longitude"] = $row[11];
                    $param["latitude"] = $row[12];
                    $param["rent_amount"] = $row[13];
                    $param["maintenance_amount"] = $row[14];
                    $param["owner_name"] = $row[15];
                    $param["owner_mobile_no"] = $row[16];
                    $param["owner_email"] = $row[17];
                    $vacancyStatus = ucwords($row[18]);

                    $param["vacancy_status"] = str_replace("Fully ", "", $vacancyStatus);
                    $param['active_status'] = '1';
                    $param['created_date'] = $current_date;
                    $total_rooms = $row[19];
                    $AmpFlat = $this->AmpFlats->patchEntity($AmpFlat, $param);
                    if ($this->AmpFlats->save($AmpFlat)) {
                        if ($total_rooms > 0) {
                            $roomFlatMapping = TableRegistry::get('amp_flat_rooms_mapping');
                            for ($i = 1; $i <= $total_rooms; $i++) {
                                $queryInsert = $roomFlatMapping->query();
                                $queryInsert->insert(['room_no', 'band', 'capacity', 'flat_id'])
                                    ->values([
                                        'room_no' => $i,
                                        'band' => $param["flat_band"],
                                        'capacity' => 1,
                                        'flat_id' => $AmpFlat->id,
                                    ])->execute();
                            }
                        }
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

    public function getaccomodationdetails()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $emp_id = $this->request->getData('employee_id');
            $emp_id = (int)$emp_id;
            $roomEmployeeMappingTable = TableRegistry::get('RoomEmpMap', ['table' => 'amp_room_employee_mapping']);
            $checkEmployeeFlat = $roomEmployeeMappingTable->find('all')->where(['RoomEmpMap.employee_id' => $emp_id, 'RoomEmpMap.active_status' => '1'])->first();
            if (!empty($checkEmployeeFlat)) {
                $checkEmployeeFlat = $checkEmployeeFlat->toArray();
                $options = array();
                $options['conditions']['id'] = $checkEmployeeFlat['flat_id'];
                $options['fields'] = array('id', 'flat_no', 'apartment_name', 'flat_type', 'flat_band', 'agreement_status', 'agreement_date', 'address', 'pincode', 'city', 'state', 'google_map_link', 'rent_amount', 'maintenance_amount', 'owner_name', 'owner_mobile_no', 'owner_email', 'vacancy_status', 'created_date', 'active_status');

                $flat = $this->AmpFlats->find('all', $options)->group('AmpFlats.id')->first()->toArray();
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
                $subOptions['order'] = 'Room.id ASC';
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
                    $band_vacancy[] = array('band' => (int) $room['room_band'], 'vacancy' => $room['room_vacancy']);
                }

                $flat['flat_vacancy'] = $band_vacancy;
                $flat['vacancy_count'] = $vacancy_count;
                $flat['rent_amount'] = moneyFormatIndia((int) $flat['rent_amount']);
                $flat['flat_band'] = (int) $flat['flat_band'];
                $flat['maintenance_amount'] = moneyFormatIndia((int) $flat['maintenance_amount']);
                if ($flat['agreement_status'] == 'Pending') {
                    $flat['agreement_date'] = '';
                } else {
                    $flat['agreement_date'] = date("Y-m-d", strtotime($flat['agreement_date']));
                }
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
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }
}

function moneyFormatIndia($num)
{
    $explrestunits = "";
    if (strlen($num) > 3) {
        $lastthree = substr($num, strlen($num) - 3, strlen($num));
        $restunits = substr($num, 0, strlen($num) - 3); // extracts the last three digits
        $restunits = (strlen($restunits) % 2 == 1) ? "0" . $restunits : $restunits; // explodes the remaining digits in 2's formats, adds a zero in the beginning to maintain the 2's grouping.
        $expunit = str_split($restunits, 2);
        for ($i = 0; $i < sizeof($expunit); $i++) {
            // creates each of the 2's group and adds a comma to the end
            if ($i == 0) {
                $explrestunits .= (int) $expunit[$i] . ","; // if is first value , convert into integer
            } else {
                $explrestunits .= $expunit[$i] . ",";
            }
        }
        $thecash = $explrestunits . $lastthree;
    } else {
        $thecash = $num;
    }
    return $thecash; // writes the final format where $currency is the currency symbol.
}
