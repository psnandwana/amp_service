<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AmpFlatEmployeesMapping Model
 *
 * @property \App\Model\Table\FlatsTable&\Cake\ORM\Association\BelongsTo $Flats
 * @property \App\Model\Table\EmployeesTable&\Cake\ORM\Association\BelongsTo $Employees
 *
 * @method \App\Model\Entity\AmpFlatEmployeesMapping get($primaryKey, $options = [])
 * @method \App\Model\Entity\AmpFlatEmployeesMapping newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\AmpFlatEmployeesMapping[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AmpFlatEmployeesMapping|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AmpFlatEmployeesMapping saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AmpFlatEmployeesMapping patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AmpFlatEmployeesMapping[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\AmpFlatEmployeesMapping findOrCreate($search, callable $callback = null, $options = [])
 */
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
