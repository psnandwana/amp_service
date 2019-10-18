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

        $this->belongsToMany('AmpEmployeesListing', [
            'through' => 'AmpFlatEmployeesMapping',
            'foreignKey' => 'flat_id',
            'targetForeignKey' => 'employee_id',
          ]);
    }  

}