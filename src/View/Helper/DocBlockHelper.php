<?php
declare(strict_types=1);

namespace Bake\View\Helper;

use Cake\Collection\Collection;
use Cake\Core\App;
use Cake\Database\Type\EnumType;
use Cake\Database\TypeFactory;
use Cake\ORM\Association;
use Cake\Utility\Inflector;
use Cake\View\Helper;

/**
 * DocBlock helper
 */
class DocBlockHelper extends Helper
{
    /**
     * @var bool Whether to add a blank line between different class annotations
     */
    protected bool $_annotationSpacing = true;

    /**
     * Writes the DocBlock header for a class which includes the property and method declarations. Annotations are
     * sorted and grouped by type and value. Groups of annotations are separated by blank lines.
     *
     * @param string $className The class this comment block is for.
     * @param string $classType The type of class (example, Entity)
     * @param array<string> $annotations An array of PHP comment block annotations.
     * @return string The DocBlock for a class header.
     */
    public function classDescription(string $className, string $classType, array $annotations): string
    {
        $lines = [];
        if ($className && $classType) {
            $lines[] = "{$className} {$classType}";
        }

        if ($annotations && $lines) {
            $lines[] = '';
        }

        $previous = false;
        foreach ($annotations as $annotation) {
            if (strlen($annotation) > 1 && $annotation[0] === '@' && strpos($annotation, ' ') > 0) {
                $type = substr($annotation, 0, strpos($annotation, ' '));
                if (
                    $this->_annotationSpacing &&
                    $previous !== false &&
                    $previous !== $type
                ) {
                    $lines[] = '';
                }
                $previous = $type;
            }
            $lines[] = $annotation;
        }

        $lines = array_merge(['/**'], (new Collection($lines))->map(function ($line) {
            return rtrim(" * {$line}");
        })->toArray(), [' */']);

        return implode("\n", $lines);
    }

    /**
     * Converts an entity class type to its DocBlock hint type counterpart.
     *
     * @param string $type The entity class type (a fully qualified class name).
     * @param \Cake\ORM\Association $association The association related to the entity class.
     * @return string The DocBlock type
     */
    public function associatedEntityTypeToHintType(string $type, Association $association): string
    {
        $annotationType = $association->type();
        if (
            $annotationType === Association::MANY_TO_MANY ||
            $annotationType === Association::ONE_TO_MANY
        ) {
            return $type . '[]';
        }

        return $type;
    }

    /**
     * Builds a map of entity columns as DocBlock types for use
     * in generating `@property` hints.
     *
     * This method expects a property schema as generated by
     * `\Bake\Command\ModelCommand::getEntityPropertySchema()`.
     *
     * The generated map has the format of
     *
     * ```
     * [
     *     'property-name' => 'doc-block-type',
     *     ...
     * ]
     * ```
     *
     * @see \Bake\Command\ModelCommand::getEntityPropertySchema
     * @param array $propertySchema The property schema to use for generating the type map.
     * @return array The property DocType map.
     */
    public function buildEntityPropertyHintTypeMap(array $propertySchema): array
    {
        $properties = [];
        foreach ($propertySchema as $property => $info) {
            if ($info['kind'] === 'column') {
                $type = $this->columnTypeToHintType($info['type']);
                if (!empty($info['null'])) {
                    $type .= '|null';
                }

                $properties[$property] = $type;
            }
        }

        return $properties;
    }

    /**
     * Builds a map of entity associations as DocBlock types for use
     * in generating `@property` hints.
     *
     * This method expects a property schema as generated by
     * `\Bake\Command\ModelCommand::getEntityPropertySchema()`.
     *
     * The generated map has the format of
     *
     * ```
     * [
     *     'property-name' => 'doc-block-type',
     *     ...
     * ]
     * ```
     *
     * @see \Bake\Command\ModelCommand::getEntityPropertySchema
     * @param array $propertySchema The property schema to use for generating the type map.
     * @return array The property DocType map.
     */
    public function buildEntityAssociationHintTypeMap(array $propertySchema): array
    {
        $properties = [];
        foreach ($propertySchema as $property => $info) {
            if ($info['kind'] === 'association') {
                $type = $this->associatedEntityTypeToHintType($info['type'], $info['association']);
                if ($info['association']->type() === Association::MANY_TO_ONE) {
                    $properties = $this->_insertAfter(
                        $properties,
                        $info['association']->getForeignKey(),
                        [$property => $type]
                    );
                } else {
                    $properties[$property] = $type;
                }
            }
        }

        return $properties;
    }

    /**
     * Converts a column type to its DocBlock type counterpart.
     *
     * This method only supports the default CakePHP column types,
     * for custom column/database types `'string'` will be returned.
     *
     * @see \Cake\Database\Type
     * @param string $type The column type.
     * @return string|null The DocBlock type, or `'string'` for unsupported column types.
     */
    public function columnTypeToHintType(string $type): ?string
    {
        switch ($type) {
            case 'char':
            case 'string':
            case 'text':
            case 'uuid':
            case 'decimal':
                return 'string';

            case 'integer':
            case 'biginteger':
            case 'smallinteger':
            case 'tinyinteger':
                return 'int';

            case 'float':
                return 'float';

            case 'boolean':
                return 'bool';

            case 'array':
            case 'json':
                return 'array';

            case 'binary':
                return 'string|resource';

            case 'date':
                $dbType = TypeFactory::build($type);
                if (method_exists($dbType, 'getDateClassName')) {
                    // allow custom Date class which should extend \Cake\I18n\Date
                    return '\\' . $dbType->getDateClassName();
                }

                return '\Cake\I18n\Date';

            case 'datetime':
            case 'datetimefractional':
            case 'timestamp':
            case 'timestampfractional':
            case 'timestamptimezone':
                $dbType = TypeFactory::build($type);
                if (method_exists($dbType, 'getDateTimeClassName')) {
                    // allow custom DateTime class which should extend \Cake\I18n\DateTime
                    return '\\' . $dbType->getDateTimeClassName();
                }

                return '\Cake\I18n\DateTime';

            case 'time':
                $dbType = TypeFactory::build($type);
                if (method_exists($dbType, 'getTimeClassName')) {
                    // allow custom Time class which should extend \Cake\I18n\Time
                    return '\\' . $dbType->getTimeClassName();
                }

                return '\Cake\I18n\Time';

            default:
                if (str_starts_with($type, 'enum-')) {
                    $dbType = TypeFactory::build($type);
                    if ($dbType instanceof EnumType) {
                        return '\\' . $dbType->getEnumClassName();
                    }
                }
        }

        // Any unique or custom types will have a `string` type hint
        return 'string';
    }

    /**
     * Renders a map of DocBlock property types as an array of
     * `@property` hints.
     *
     * @param array<string> $properties A key value pair where key is the name of a property and the value is the type.
     * @return array<string>
     */
    public function propertyHints(array $properties): array
    {
        $lines = [];
        foreach ($properties as $property => $type) {
            $type = $type ? $type . ' ' : '';
            $lines[] = "@property {$type}\${$property}";
        }

        return $lines;
    }

    /**
     * Build property, method, mixing annotations for table class.
     *
     * @param array $associations Associations list.
     * @param array $associationInfo Association info.
     * @param array $behaviors Behaviors list.
     * @param string $entity Entity name.
     * @param string $namespace Namespace.
     * @return array<string>
     */
    public function buildTableAnnotations(
        array $associations,
        array $associationInfo,
        array $behaviors,
        string $entity,
        string $namespace
    ): array {
        $annotations = [];
        foreach ($associations as $type => $assocs) {
            foreach ($assocs as $assoc) {
                $typeStr = Inflector::camelize($type);
                if (isset($associationInfo[$assoc['alias']])) {
                    $tableFqn = $associationInfo[$assoc['alias']]['targetFqn'];
                    $annotations[] = "@property {$tableFqn}&\Cake\ORM\Association\\{$typeStr} \${$assoc['alias']}";
                }
            }
        }

        $entityPath = "\\{$namespace}\\Model\\Entity\\{$entity}";

        // phpcs:disable
        $annotations[] = "@method $entityPath newEmptyEntity()";
        $annotations[] = "@method $entityPath newEntity(array<mixed> \$data, array<string, mixed> \$options = [])";
        $annotations[] = "@method array<$entityPath> newEntities(array<mixed> \$data, array<string, mixed> \$options = [])";
        $annotations[] = "@method $entityPath get(mixed \$primaryKey, array<mixed>|string \$finder = 'all', \\Psr\\SimpleCache\\CacheInterface|string|null \$cache = null, \Closure|string|null \$cacheKey = null, mixed ...\$args)";
        $annotations[] = "@method $entityPath findOrCreate(\$search, ?callable \$callback = null, array<string, mixed> \$options = [])";
        $annotations[] = "@method $entityPath patchEntity(\\Cake\\Datasource\\EntityInterface \$entity, array<mixed> \$data, array<string, mixed> \$options = [])";
        $annotations[] = "@method array<$entityPath> patchEntities(iterable<\\Cake\\Datasource\\EntityInterface> \$entities, array<mixed> \$data, array<string, mixed> \$options = [])";
        $annotations[] = "@method $entityPath|false save(\\Cake\\Datasource\\EntityInterface \$entity, array<string, mixed> \$options = [])";
        $annotations[] = "@method $entityPath saveOrFail(\\Cake\\Datasource\\EntityInterface \$entity, array<string, mixed> \$options = [])";
        $annotations[] = "@method iterable<$entityPath>|\Cake\Datasource\ResultSetInterface<$entityPath>|false saveMany(iterable<\\Cake\\Datasource\\EntityInterface> \$entities, array<string, mixed> \$options = [])";
        $annotations[] = "@method iterable<$entityPath>|\Cake\Datasource\ResultSetInterface<$entityPath> saveManyOrFail(iterable<\\Cake\\Datasource\\EntityInterface> \$entities, array<string, mixed> \$options = [])";
        $annotations[] = "@method iterable<$entityPath>|\Cake\Datasource\ResultSetInterface<$entityPath>|false deleteMany(iterable<\\Cake\\Datasource\\EntityInterface> \$entities, array<string, mixed> \$options = [])";
        $annotations[] = "@method iterable<$entityPath>|\Cake\Datasource\ResultSetInterface<$entityPath> deleteManyOrFail(iterable<\\Cake\\Datasource\\EntityInterface> \$entities, array<string, mixed> \$options = [])";
        // phpcs:enable
        foreach ($behaviors as $behavior => $behaviorData) {
            $className = App::className($behavior, 'Model/Behavior', 'Behavior');
            if (!$className) {
                $className = "Cake\ORM\Behavior\\{$behavior}Behavior";
            }

            $annotations[] = '@mixin \\' . $className;
        }

        return $annotations;
    }

    /**
     * Inserts a value after a specific key in an associative array.
     *
     * In case the given key cannot be found, the value will be appended.
     *
     * @param array $target The array in which to insert the new value.
     * @param string $key The array key after which to insert the new value.
     * @param mixed $value The entry to insert.
     * @return array The array with the new value inserted.
     */
    protected function _insertAfter(array $target, string $key, mixed $value): array
    {
        $index = array_search($key, array_keys($target));
        if ($index !== false) {
            $target = array_merge(
                array_slice($target, 0, $index + 1),
                $value,
                array_slice($target, $index + 1, null)
            );
        } else {
            $target += (array)$value;
        }

        return $target;
    }
}
