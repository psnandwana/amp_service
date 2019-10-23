<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;


class AmpGrievance extends Entity
{
    protected $_accessible = [
        'employee_id' => true,
        'request_type' => true,
        'subject' => true,
        'description' => true,
        'status' => true,
        'submitted_date' => true,
        'employee' => true
    ];
}
