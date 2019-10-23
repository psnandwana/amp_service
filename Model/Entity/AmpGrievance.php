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
        'employee' => true,
        'rm_id' => true,
        'rm_approval_status' => true,
        'rm_approval_date' => true,
        'approved_by' => true,
        'approved_date' => true
    ];
}
