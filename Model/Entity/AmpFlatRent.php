<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AmpFlatRent Entity
 *
 * @property int $id
 * @property int|null $flat_id
 * @property int|null $rent_month
 * @property int|null $rent_year
 * @property float|null $rent_amount
 * @property \Cake\I18n\FrozenTime|null $payment_date
 * @property int|null $payment_by
 *
 * @property \App\Model\Entity\Flat $flat
 */
class AmpFlatRent extends Entity
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
        'rent_month' => true,
        'rent_year' => true,
        'rent_amount' => true,
        'payment_date' => true,
        'payment_by' => true,
        'flat' => true
    ];
}
