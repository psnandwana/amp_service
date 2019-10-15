<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AmpLocations Model
 *
 * @method \App\Model\Entity\AmpLocation get($primaryKey, $options = [])
 * @method \App\Model\Entity\AmpLocation newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\AmpLocation[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AmpLocation|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AmpLocation saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AmpLocation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AmpLocation[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\AmpLocation findOrCreate($search, callable $callback = null, $options = [])
 */
class AmpLocationsTable extends Table
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

        $this->setTable('amp_locations');
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
            ->scalar('location')
            ->maxLength('location', 255)
            ->requirePresence('location', 'create')
            ->notEmptyString('location');

        $validator
            ->scalar('address')
            ->allowEmptyString('address');

        $validator
            ->scalar('state')
            ->maxLength('state', 50)
            ->allowEmptyString('state');

        $validator
            ->scalar('city')
            ->maxLength('city', 50)
            ->allowEmptyString('city');

        $validator
            ->scalar('pin_code')
            ->maxLength('pin_code', 10)
            ->allowEmptyString('pin_code');

        $validator
            ->scalar('longitude')
            ->maxLength('longitude', 50)
            ->allowEmptyString('longitude');

        $validator
            ->scalar('latitude')
            ->maxLength('latitude', 50)
            ->allowEmptyString('latitude');

        return $validator;
    }
}
