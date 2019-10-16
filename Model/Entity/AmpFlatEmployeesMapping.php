<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AmpFlatEmployeesMapping Entity
 *
 * @property int $id
 * @property int $flat_id
 * @property int $employee_id
 * @property int $assigned_by
 * @property \Cake\I18n\FrozenTime $assigned_date
 *
 * @property \App\Model\Entity\Flat $flat
 * @property \App\Model\Entity\Employee $employee
 */
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
        'flat_id' => true,
        'employee_id' => true,
        'assigned_by' => true,
        'assigned_date' => true,
        'flat' => true,
        'employee' => true
    ];
}
