<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;


class AmpFlatEmployeesMappingTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('amp_flat_employees_mapping');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('AmpFlats', [
            'foreignKey' => 'flat_id',
            'joinType' => 'INNER'
        ]);

        $this->belongsTo('AmpEmployeesListing', [
            'foreignKey' => 'employee_id',
            'joinType' => 'INNER'
        ]);
    }
}
