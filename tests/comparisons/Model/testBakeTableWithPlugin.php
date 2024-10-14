<?php
declare(strict_types=1);

namespace BakeTest\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @property \BakeTest\Model\Table\CommentsTable&\Cake\ORM\Association\HasMany $Comments
 * @property \BakeTest\Model\Table\TodoItemsTable&\Cake\ORM\Association\HasMany $TodoItems
 *
 * @method \BakeTest\Model\Entity\User newEmptyEntity()
 * @method \BakeTest\Model\Entity\User newEntity(array $data, array $options = [])
 * @method array<\BakeTest\Model\Entity\User> newEntities(array $data, array $options = [])
 * @method \BakeTest\Model\Entity\User get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \BakeTest\Model\Entity\User findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \BakeTest\Model\Entity\User patchEntity(\BakeTest\Model\Entity\User $entity, array $data, array $options = [])
 * @method array<\BakeTest\Model\Entity\User> patchEntities(iterable<\BakeTest\Model\Entity\User> $entities, array $data, array $options = [])
 * @method \BakeTest\Model\Entity\User|false save(\BakeTest\Model\Entity\User $entity, array $options = [])
 * @method \BakeTest\Model\Entity\User saveOrFail(\BakeTest\Model\Entity\User $entity, array $options = [])
 * @method iterable<\BakeTest\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\BakeTest\Model\Entity\User>|false saveMany(iterable<\BakeTest\Model\Entity\User> $entities, array $options = [])
 * @method iterable<\BakeTest\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\BakeTest\Model\Entity\User> saveManyOrFail(iterable<\BakeTest\Model\Entity\User> $entities, array $options = [])
 * @method iterable<\BakeTest\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\BakeTest\Model\Entity\User>|false deleteMany(iterable<\BakeTest\Model\Entity\User> $entities, array $options = [])
 * @method iterable<\BakeTest\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\BakeTest\Model\Entity\User> deleteManyOrFail(iterable<\BakeTest\Model\Entity\User> $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Comments', [
            'foreignKey' => 'user_id',
            'className' => 'BakeTest.Comments',
        ]);
        $this->hasMany('TodoItems', [
            'foreignKey' => 'user_id',
            'className' => 'BakeTest.TodoItems',
        ]);
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
}
