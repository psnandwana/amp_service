<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AmpAdminUser Model
 *
 * @method \App\Model\Entity\AmpAdminUser get($primaryKey, $options = [])
 * @method \App\Model\Entity\AmpAdminUser newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\AmpAdminUser[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AmpAdminUser|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AmpAdminUser saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AmpAdminUser patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AmpAdminUser[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\AmpAdminUser findOrCreate($search, callable $callback = null, $options = [])
 */
class AmpAdminUserTable extends Table
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

        $this->setTable('amp_admin_user');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->belongsTo('AmpLocation')
            ->setForeignKey('campaign_office')
            ->setJoinType('INNER');
    }
   
}
