<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AmpFlat Entity
 *
 * @property int $id
 * @property int|null $flat_no
 * @property string|null $apartment_name
 * @property int|null $flat_type
 * @property string|null $agreement_status
 * @property \Cake\I18n\FrozenDate|null $agreement_date
 * @property string|null $address
 * @property string|null $pincode
 * @property string|null $city
 * @property string|null $state
 * @property string|null $google_map_link
 * @property string|null $longitude
 * @property string|null $latitude
 * @property float|null $rent_amount
 * @property float|null $maintenance_amount
 * @property string|null $owner_name
 * @property string|null $owner_mobile_no
 * @property string|null $owner_email
 * @property string|null $vacancy_status
 * @property \Cake\I18n\FrozenTime $created_date
 * @property string $active_status
 *
 * @property \App\Model\Entity\AmpEmployeesListing[] $amp_employees_listing
 */
class AmpFlat extends Entity
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
        'flat_no' => true,
        'apartment_name' => true,
        'flat_type' => true,
        'flat_band' => true,
        'agreement_status' => true,
        'agreement_date' => true,
        'address' => true,
        'pincode' => true,
        'city' => true,
        'state' => true,
        'google_map_link' => true,
        'longitude' => true,
        'latitude' => true,
        'rent_amount' => true,
        'maintenance_amount' => true,
        'owner_name' => true,
        'owner_mobile_no' => true,
        'owner_email' => true,
        'vacancy_status' => true,
        'created_date' => true,
        'active_status' => true,
        'owner_details' => true,
        'amp_employees_listing' => true
    ];
}
