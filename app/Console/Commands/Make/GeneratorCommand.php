<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\GeneratorCommand as LaravelGeneratorCommand;
use Illuminate\Support\Str;

abstract class GeneratorCommand extends LaravelGeneratorCommand
{
    /**
     * Build the class with the given name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $stub = str_replace(
            ['DummyRepositoryInterfaceNamespace',
                'DummyRepositoryImplementationNamespace',
                'NamespacedDummyRepositoryInterface',
                'dummyClass',
            ],
            [$this->getRepositoryInterfaceNamespace($name),
                $this->getRepositoryImplementationNamespace($name),
                $this->getNamespacedRepositoryInterface($name),
                $this->getClassInstance($name),
            ],
            $stub
        );

        return $stub;
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return 'HMS';
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getPath($name)
    {
        return $this->laravel['path'] . '/' . str_replace('\\', '/', $name) . '.php';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return config('repositories.entity_namespace');
    }

    /**
     * Get the namespaced repository interface for the class.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getNamespacedRepositoryInterface($name)
    {
        $name = str_replace($this->getDefaultNamespace($name) . '\\', '', $name);
        $interface = config('repositories.repository_namespace') . '\\' . $name . 'Repository';

        return $interface;
    }

    /**
     * Get the repository interface namespace for the class.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getRepositoryInterfaceNamespace($name)
    {
        $interface = $this->getNamespacedRepositoryInterface($name);

        return trim(implode('\\', array_slice(explode('\\', $interface), 0, -1)), '\\');
    }

    /**
     * Get the namespaced repository implementation for the class.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getNamespacedRepositoryImplementation($name)
    {
        $name = str_replace($this->getDefaultNamespace($name) . '\\', '', $name);
        preg_match('/(.*?)(\\\\)*([^\\\\]+)$/', $name, $matches);
        if (isset($matches[2])) {
            $implementation = config('repositories.repository_namespace') . '\\' .
            $matches[1] .
            '\\Doctrine\\Doctrine' .
            $matches[3] .
            'Repository';
        } else {
            $implementation = config('repositories.repository_namespace') . '\\' .
            'Doctrine\\Doctrine' .
            $matches[3] .
            'Repository';
        }

        return $implementation;
    }

    /**
     * Get the repository implementation namespace for the class.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getRepositoryImplementationNamespace($name)
    {
        $implementation = $this->getNamespacedRepositoryImplementation($name);

        return trim(implode('\\', array_slice(explode('\\', $implementation), 0, -1)), '\\');
    }

    /**
     * Get the camelcase name for the class.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getClassInstance($name)
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        return Str::camel($class);
    }
}
