<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ParseTestTable Model
 *
 * @method \Bake\Test\App\Model\Entity\ParseTestTable newEmptyEntity()
 * @method \Bake\Test\App\Model\Entity\ParseTestTable newEntity(array<mixed> $data, array<string, mixed> $options = [])
 * @method array<\Bake\Test\App\Model\Entity\ParseTestTable> newEntities(array<mixed> $data, array<string, mixed> $options = [])
 * @method \Bake\Test\App\Model\Entity\ParseTestTable get($primaryKey, array $options = [])
 * @method \Bake\Test\App\Model\Entity\ParseTestTable findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \Bake\Test\App\Model\Entity\ParseTestTable patchEntity(\Bake\Test\App\Model\Entity\ParseTestTable $entity, array $data, array $options = [])
 * @method array<\Bake\Test\App\Model\Entity\ParseTestTable> patchEntities(iterable<\Cake\Datasource\EntityInterface> $entities, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\ParseTestTable|false save(\Bake\Test\App\Model\Entity\ParseTestTable $entity, array $options = [])
 * @method \Bake\Test\App\Model\Entity\ParseTestTable saveOrFail(\Bake\Test\App\Model\Entity\ParseTestTable $entity, array $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\ParseTestTable>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\ParseTestTable>|false saveMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\ParseTestTable>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\ParseTestTable> saveManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\ParseTestTable>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\ParseTestTable>|false deleteMany(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\ParseTestTable>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\ParseTestTable> deleteManyOrFail(iterable<\Cake\Datasource\EntityInterface> $entities, array<string, mixed> $options = [])
 */
class ParseTestTable extends Table
{
    /**
     * @var int
     */
    #[SomeAttribute]
    protected const SOME_CONST = 1;

    /**
     * @var string
     */
    #[SomeAttribute]
    protected $withDocProperty = <<<'TEXT'
    BLOCK OF TEXT
TEXT;

    protected $withoutDocProperty = 1;

    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('test');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['username']), ['errorField' => 'username']);

        return $rules;
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->numeric('id')
            ->allowEmptyString('id', 'create');

        $validator
            ->scalar('username')
            ->maxLength('username', 100, 'Name must be shorter than 100 characters.')
            ->requirePresence('username', 'create')
            ->allowEmptyString('username', null, false);

        return $validator;
    }

    /**
     * Returns the database connection name to use by default.
     *
     * @return string
     */
    public static function defaultConnectionName(): string
    {
        return 'test';
    }

    /**
     * @param \Cake\ORM\Query\SelectQuery $query Finder query
     * @param array $options Finder options
     * @return \Cake\ORM\Query\SelectQuery
     */
    #[SomeAttribute]
    public function findAttributes(SelectQuery $query, array $options): SelectQuery
    {
        return $query;
    }

    /**
     * @param \Cake\ORM\Query\SelectQuery $query Finder query
     * @param array $options Finder options
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findNoAttributes(SelectQuery $query, array $options): SelectQuery
    {
        return $query;
    }
}
