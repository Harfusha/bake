<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         2.8.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\CodeGen;

use Cake\Console\ConsoleIo;

class FileBuilder
{
    /**
     * @var \Cake\Console\ConsoleIo
     */
    protected ConsoleIo $io;

    /**
     * @var string
     */
    protected string $namespace;

    /**
     * @var \Bake\CodeGen\ParsedFile|null
     */
    protected ?ParsedFile $parsedFile;

    /**
     * @var \Bake\CodeGen\ClassBuilder
     */
    protected ClassBuilder $classBuilder;

    /**
     * @param \Cake\Console\ConsoleIo $io Console io
     * @param string $namespace File namespace
     * @param \Bake\CodeGen\ParsedFile $parsedFile Parsed file it already exists
     */
    public function __construct(ConsoleIo $io, string $namespace, ?ParsedFile $parsedFile = null)
    {
        if ($parsedFile && $parsedFile->namespace !== $namespace) {
            throw new ParseException(sprintf(
                'Existing namespace `%s` does not match expected namespace `%s`, cannot update existing file',
                $parsedFile->namespace,
                $namespace
            ));
        }

        $this->io = $io;
        $this->namespace = $namespace;
        $this->parsedFile = $parsedFile;
        $this->classBuilder = new ClassBuilder($parsedFile->class ?? null);
    }

    /**
     * Returns the file namespace.
     *
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return \Bake\CodeGen\ClassBuilder
     */
    public function classBuilder(): ClassBuilder
    {
        return $this->classBuilder;
    }

    /**
     * Returns class imports merged with user imports from file.
     *
     * @param array<string|int, string> $imports Class imports to merge with file imports
     * @return array<string, string>
     */
    public function getClassImports(array $imports = []): array
    {
        return $this->mergeFileImports($this->normalizeImports($imports), $this->parsedFile->classImports ?? []);
    }

    /**
     * Returns function imports merged with user imports from file.
     *
     * @param array<string|int, string> $imports Function imports to merge with file imports
     * @return array<string, string>
     */
    public function getFunctionImports(array $imports = []): array
    {
        return $this->mergeFileImports($this->normalizeImports($imports), $this->parsedFile->functionImports ?? []);
    }

    /**
     * Returns const imports merged with user imports from file.
     *
     * @param array<string|int, string> $imports Const imports to merge with file imports
     * @return array<string, string>
     */
    public function getConstImports(array $imports = []): array
    {
        return $this->mergeFileImports($this->normalizeImports($imports), $this->parsedFile->constImports ?? []);
    }

    /**
     * Normalizes imports included from generated code into [alias => name] format.
     *
     * @param array<string|int, string> $imports Imports
     * @return array<string, string>
     */
    protected function normalizeImports(array $imports): array
    {
        $normalized = [];
        foreach ($imports as $alias => $class) {
            if (is_int($alias)) {
                $last = strrpos($class, '\\', -1);
                if ($last !== false) {
                    $alias = substr($class, strrpos($class, '\\', -1) + 1);
                } else {
                    $alias = $class;
                }
            }

            $normalized[$alias] = $class;
        }

        return $normalized;
    }

    /**
     * @param array<string, string> $imports Imports
     * @param array<string, string> $fileImports File imports to merge
     * @return array<string, string>
     */
    protected function mergeFileImports(array $imports, array $fileImports): array
    {
        foreach ($fileImports as $alias => $class) {
            if (isset($imports[$alias]) && $imports[$alias] !== $class) {
                $this->io->warning(sprintf(
                    'File import `%s` conflicts with generated import, discarding',
                    $class
                ));
                continue;
            }

            $existingAlias = array_search($class, $imports, true);
            if ($existingAlias !== false && $existingAlias != $alias) {
                $this->io->warning(sprintf(
                    'File import `%s` conflicts with generated import, discarding',
                    $class
                ));
                continue;
            }

            $imports[$alias] = $class;
        }

        asort($imports, SORT_STRING | SORT_FLAG_CASE);

        return $imports;
    }
}
