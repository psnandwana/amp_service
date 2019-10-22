<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AmpAdminUser Entity
 *
 */
class AmpAdminUser extends Entity
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
        'name' => true,
        'mobile_no' => true,
        'email' => true,
        'password' => true,
        'campaign_office' => true,
        'super_admin' => true,
        'admin' => true,
        'view' => true,
        'employee' => true,
        'view_download' => true,
        'created_date' => true
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
        'password'
    ];
}
