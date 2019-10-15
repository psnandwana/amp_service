<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AmpLocation Entity
 *
 * @property int $id
 * @property string $location
 * @property string|null $address
 * @property string|null $state
 * @property string|null $city
 * @property string|null $pin_code
 * @property string|null $longitude
 * @property string|null $latitude
 */
class AmpLocation extends Entity
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
        'location' => true,
        'address' => true,
        'state' => true,
        'city' => true,
        'pin_code' => true,
        'longitude' => true,
        'latitude' => true
    ];
}
