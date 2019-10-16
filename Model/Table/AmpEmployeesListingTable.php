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

        $this->belongsToMany('AmpFlats', [
            'through' => 'AmpFlatEmployeesMapping',
            'foreignKey' => 'employee_id',
            'targetForeignKey' => 'flat_id',
          ]);


    }
}
