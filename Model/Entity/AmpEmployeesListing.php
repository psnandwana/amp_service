<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AmpEmployeesListing Entity
 *
 * @property int $id
 * @property string|null $emp_code
 * @property string|null $emp_name
 * @property string|null $email_id
 * @property string|null $password
 *
 * @property \App\Model\Entity\Email $email
 */
class AmpEmployeesListing extends Entity
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
        'emp_code' => true,
        'emp_name' => true,
        'email_id' => true,
        'email' => true,
        'rm_email_id' => true,
        'team' => true,
        'phone' => true,
        'acco_model_name' => true,
        'rm_name' => true
    ];
}
