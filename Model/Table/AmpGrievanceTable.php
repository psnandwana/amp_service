<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;


class AmpGrievanceTable extends Table
{
    
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('amp_grievance');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

    }

}
