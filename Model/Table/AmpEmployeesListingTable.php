<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AmpEmployeesListing Model
 *
 * @property \App\Model\Table\EmailsTable&\Cake\ORM\Association\BelongsTo $Emails
 *
 * @method \App\Model\Entity\AmpEmployeesListing get($primaryKey, $options = [])
 * @method \App\Model\Entity\AmpEmployeesListing newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\AmpEmployeesListing[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AmpEmployeesListing|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AmpEmployeesListing saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AmpEmployeesListing patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AmpEmployeesListing[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\AmpEmployeesListing findOrCreate($search, callable $callback = null, $options = [])
 */
class AmpEmployeesListingTable extends Table
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

        $this->setTable('amp_employees_listing');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Emails', [
            'foreignKey' => 'email_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('emp_code')
            ->maxLength('emp_code', 100)
            ->allowEmptyString('emp_code');

        $validator
            ->scalar('emp_name')
            ->maxLength('emp_name', 255)
            ->allowEmptyString('emp_name');

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->allowEmptyString('password');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['email_id'], 'Emails'));

        return $rules;
    }
}
