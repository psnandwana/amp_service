<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AmpFlats Model
 *
 * @method \App\Model\Entity\AmpFlat get($primaryKey, $options = [])
 * @method \App\Model\Entity\AmpFlat newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\AmpFlat[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AmpFlat|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AmpFlat saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AmpFlat patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AmpFlat[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\AmpFlat findOrCreate($search, callable $callback = null, $options = [])
 */
class AmpFlatsTable extends Table
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

        $this->setTable('amp_flats');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
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
            ->integer('flat_no')
            ->allowEmptyString('flat_no');

        $validator
            ->scalar('apartment_name')
            ->maxLength('apartment_name', 255)
            ->allowEmptyString('apartment_name');

        $validator
            ->integer('flat_type')
            ->allowEmptyString('flat_type');

        $validator
            ->scalar('agreement_status')
            ->maxLength('agreement_status', 10)
            ->allowEmptyString('agreement_status');

        $validator
            ->dateTime('agreement_date')
            ->allowEmptyDateTime('agreement_date');

        $validator
            ->scalar('address')
            ->allowEmptyString('address');

        $validator
            ->scalar('pincode')
            ->maxLength('pincode', 10)
            ->allowEmptyString('pincode');

        $validator
            ->scalar('city')
            ->maxLength('city', 50)
            ->allowEmptyString('city');

        $validator
            ->scalar('state')
            ->maxLength('state', 50)
            ->allowEmptyString('state');

        $validator
            ->scalar('longitude')
            ->maxLength('longitude', 50)
            ->allowEmptyString('longitude');

        $validator
            ->scalar('latitude')
            ->maxLength('latitude', 50)
            ->allowEmptyString('latitude');

        $validator
            ->decimal('rent_amount')
            ->allowEmptyString('rent_amount');

        $validator
            ->decimal('maintenance_amount')
            ->allowEmptyString('maintenance_amount');

        $validator
            ->scalar('owner_name')
            ->maxLength('owner_name', 100)
            ->allowEmptyString('owner_name');

        $validator
            ->scalar('owner_mobile_no')
            ->maxLength('owner_mobile_no', 15)
            ->allowEmptyString('owner_mobile_no');

        $validator
            ->scalar('owner_email')
            ->maxLength('owner_email', 100)
            ->allowEmptyString('owner_email');

        $validator
            ->scalar('vacancy_status')
            ->allowEmptyString('vacancy_status');

        $validator
            ->integer('flat_capacity')
            ->allowEmptyString('flat_capacity');

        $validator
            ->integer('flat_band')
            ->allowEmptyString('flat_band');

        $validator
            ->dateTime('created_date')
            ->requirePresence('created_date', 'create')
            ->notEmptyDateTime('created_date');

        return $validator;
    }
}
