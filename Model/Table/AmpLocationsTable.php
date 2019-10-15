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
}
