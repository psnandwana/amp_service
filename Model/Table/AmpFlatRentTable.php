<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AmpFlatRent Model
 *
 * @property \App\Model\Table\FlatsTable&\Cake\ORM\Association\BelongsTo $Flats
 *
 * @method \App\Model\Entity\AmpFlatRent get($primaryKey, $options = [])
 * @method \App\Model\Entity\AmpFlatRent newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\AmpFlatRent[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AmpFlatRent|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AmpFlatRent saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AmpFlatRent patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AmpFlatRent[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\AmpFlatRent findOrCreate($search, callable $callback = null, $options = [])
 */
class AmpFlatRentTable extends Table
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

        $this->setTable('amp_flat_rent');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('AmpFlats', [
            'foreignKey' => 'flat_id'
        ]);
    }
}
