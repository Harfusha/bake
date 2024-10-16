<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TodoTasks Model
 *
 * @property \Bake\Test\App\Model\Table\TodoItemsTable&\Cake\ORM\Association\BelongsTo $TodoItems
 *
 * @method \Bake\Test\App\Model\Entity\TodoTask newEmptyEntity()
 * @method \Bake\Test\App\Model\Entity\TodoTask newEntity(mixed[] $data, mixed[] $options = [])
 * @method array<\Bake\Test\App\Model\Entity\TodoTask> newEntities(mixed[] $data, mixed[] $options = [])
 * @method \Bake\Test\App\Model\Entity\TodoTask get(mixed $primaryKey, string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Bake\Test\App\Model\Entity\TodoTask findOrCreate($search, ?callable $callback = null, mixed[] $options = [])
 * @method \Bake\Test\App\Model\Entity\TodoTask patchEntity(\Cake\Datasource\EntityInterface $entity, mixed[] $data, mixed[] $options = [])
 * @method array<\Bake\Test\App\Model\Entity\TodoTask> patchEntities(iterable $entities, mixed[] $data, mixed[] $options = [])
 * @method \Bake\Test\App\Model\Entity\TodoTask|false save(\Cake\Datasource\EntityInterface $entity, mixed[] $options = [])
 * @method \Bake\Test\App\Model\Entity\TodoTask saveOrFail(\Cake\Datasource\EntityInterface $entity, mixed[] $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\TodoTask>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\TodoTask>|false saveMany(iterable $entities, mixed[] $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\TodoTask>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\TodoTask> saveManyOrFail(iterable $entities, mixed[] $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\TodoTask>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\TodoTask>|false deleteMany(iterable $entities, mixed[] $options = [])
 * @method iterable<\Bake\Test\App\Model\Entity\TodoTask>|\Cake\Datasource\ResultSetInterface<\Bake\Test\App\Model\Entity\TodoTask> deleteManyOrFail(iterable $entities, mixed[] $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Cake\ORM\Behavior\CounterCacheBehavior
 */
class TodoTasksTable extends Table
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

        $this->setTable('todo_tasks');
        $this->setDisplayField('title');
        $this->setPrimaryKey('uid');

        $this->addBehavior('Timestamp');
        $this->addBehavior('CounterCache', [
            'TodoItems' => ['todo_task_count'],
        ]);

        $this->belongsTo('TodoItems', [
            'foreignKey' => 'todo_item_id',
            'joinType' => 'INNER',
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
        $rules->add($rules->existsIn(['todo_item_id'], 'TodoItems'), ['errorField' => 'todo_item_id']);

        return $rules;
    }
}
