<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;


class AmpFlatEmployeesMapping extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'room_no' => true,
        'band' => true,
        'capacity' => true,
        'flat_id' => true,
        'employee_id' => true,
        'assigned_by' => true,
        'assigned_date' => true,
        'amp_flat' => true,
        'amp_employees_listing' => true
    ];
}
