<?php

namespace phpsamurai\RepositoryPatternGenerator;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;
use function Functional\{
    compose,
    curry
};

/**
 * Class RepositoryPatternCreator
 *
 * This class generates repository and repository contract classes for a given model.
 */
class RepositoryPatternCreator
{

    /**
     * The Filesystem instance.
     *
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * Create a new repository pattern creator instance.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->_filesystem = $filesystem;
    }

    /**
     * Create a new repository and its contract for the given model.
     *
     * @param string $model The model name.
     * @param string $basePath The base path where the repository and contract will be created.
     * @return array An array containing the paths of the created repository and contract.
     *
     * @throws InvalidArgumentException If the model does not exist or if the repository or contract already exists.
     */
    public function create($model, $basePath)
    {
        $this->ensureModelExists($model);
        $this->ensureRepositoryDoesNotAlreadyExist($model);
        $this->ensureRepositoryContractDoesNotAlreadyExist($model);

        $repositoryStub = $this->getRepositoryStub();
        $contractStub = $this->getRepositoryContractStub();

        $repositoryPath = $this->getRepositoryPath($model, $basePath);
        $this->_filesystem->put($repositoryPath, $this->populateRepositoryStub($model, $repositoryStub));

        $contractPath = $this->getRepositoryContractPath($model, $basePath);
        $this->_filesystem->put($contractPath, $this->populateRepositoryContractStub($model, $contractStub));

        return [$repositoryPath, $contractPath];
    }

    /**
     * Get the full path of the model's repository.
     *
     * @param string $model The model name.
     * @param string $path The base path where the repository will be created.
     * @return string The full path of the repository.
     */
    protected function getRepositoryPath($model, $path)
    {
        $repositoriesBasePath = $path . '/' . config('repository_pattern_generator.repositories_base_path');
        if (!file_exists($repositoriesBasePath)) {
            mkdir($repositoriesBasePath, 0777, true);
        }
        return $repositoriesBasePath . '/' . $this->getRepositoryClassName($model) . '.php';
    }

    /**
     * Get the full path of the model's repository contract.
     *
     * @param string $model The model name.
     * @param string $path The base path where the repository contract will be created.
     * @return string The full path of the repository contract.
     */
    protected function getRepositoryContractPath($model, $path)
    {
        $contractBasePath = $path . '/' . config('repository_pattern_generator.repository_contract_base_path');
        if (!file_exists($contractBasePath)) {
            mkdir($contractBasePath, 0777, true);
        }
        return $contractBasePath . '/' . $this->getRepositoryContractClassName($model) . '.php';
    }

    /**
     * Replace placeholders in the repository stub.
     *
     * @param string $model The model name.
     * @param string $stub The repository stub.
     * @return string The populated repository stub.
     */
    protected function populateRepositoryStub($model, $stub)
    {
        $curriedPregReplace = curry('preg_replace');
        $replaceRepositoryClassName = $curriedPregReplace('/DummyClass/', $this->getRepositoryClassName($model));
        $replaceRepositoryContractClassName = $curriedPregReplace('/DummyContract/', $this->getRepositoryContractClassName($model));
        $replaceRepositoryContractFullyQualifiedNamespace = $curriedPregReplace('/DummyRepositoryContractNamespace/', $this->getRepositoryContractNamespace($model));
        $replaceModelFullyQualifiedNamespace = $curriedPregReplace('/DummyModelNamespace/', $this->getModelNamespace($model));
        $replaceModelClassName = $curriedPregReplace('/DummyModel/', $this->getClassName($model));
        $replaceRepositoryNamespace = $curriedPregReplace('/DummyRepositoryBaseNamespace/', $this->getRepositoryBaseNamespace());

        $createClass = compose(
            $replaceRepositoryClassName,
            $replaceRepositoryContractClassName,
            $replaceRepositoryContractFullyQualifiedNamespace,
            $replaceModelFullyQualifiedNamespace,
            $replaceModelClassName,
            $replaceRepositoryNamespace
        );

        return $createClass($stub);
    }

    /**
     * Replace placeholders in the repository contract stub.
     *
     * @param string $model The model name.
     * @param string $stub The repository contract stub.
     * @return string The populated repository contract stub.
     */
    protected function populateRepositoryContractStub($model, $stub)
    {
        $curriedPregReplace = curry('preg_replace');
        $replaceRepositoryContractClassName = $curriedPregReplace('/DummyContract/', $this->getRepositoryContractClassName($model));
        $replaceRepositoryContractBaseNamespace = $curriedPregReplace('/DummyRepositoryContractBaseNamespace/', $this->getRepositoryContractBaseNamespace());

        $createClass = compose(
            $replaceRepositoryContractClassName,
            $replaceRepositoryContractBaseNamespace
        );

        return $createClass($stub);
    }

    /**
     * Get the repository stub file.
     *
     * @return string The content of the repository stub file.
     */
    protected function getRepositoryStub()
    {
        return $this->_filesystem->get($this->stubPath() . '/repository.stub');
    }

    /**
     * Get the repository contract stub file.
     *
     * @return string The content of the repository contract stub file.
     */
    protected function getRepositoryContractStub()
    {
        return $this->_filesystem->get($this->stubPath() . '/contract.stub');
    }

    /**
     * Get the path to the stubs.
     *
     * @return string The path to the stubs.
     */
    public function stubPath()
    {
        return __DIR__ . '/stubs';
    }

    /**
     * Get the class name of the model name.
     *
     * @param string $model The model name.
     * @return string The class name.
     */
    protected function getClassName($model)
    {
        return Str::studly($model);
    }

    /**
     * Get the repository's class name.
     *
     * @param string $model The model name.
     * @return string The repository's class name.
     */
    protected function getRepositoryClassName($model)
    {
        $modelPrefix = config('repository_pattern_generator.pluralise') ? Str::plural($model) : $model;
        return $modelPrefix . 'Repository';
    }

    /**
     * Get the repository contract's class name.
     *
     * @param string $model The model name.
     * @return string The repository contract's class name.
     */
    protected function getRepositoryContractClassName($model)
    {
        $modelPrefix = config('repository_pattern_generator.pluralise') ? Str::plural($model) : $model;
        return $modelPrefix . 'RepositoryContract';
    }

    /**
     * Ensure that the model exists.
     *
     * @param string $model The model name.
     * @return void
     *
     * @throws InvalidArgumentException If the model does not exist.
     */
    protected function ensureModelExists($model)
    {
        $classNamespace = $this->getModelNamespace($model);
        if (!class_exists($classNamespace)) {
            throw new InvalidArgumentException("{$classNamespace} does not exist.");
        }
    }

    /**
     * Ensure that a repository for the given model does not already exist.
     *
     * @param string $model The model name.
     * @return void
     *
     * @throws InvalidArgumentException If the repository already exists.
     */
    protected function ensureRepositoryDoesNotAlreadyExist($model)
    {
        $classFullyQualified = $this->getRepositoryNamespace($model);
        if (class_exists($classFullyQualified, false)) {
            throw new InvalidArgumentException("{$classFullyQualified} already exists.");
        }
    }

    /**
     * Ensure that a repository contract for the given model does not already exist.
     *
     * @param string $model The model name.
     * @return void
     *
     * @throws InvalidArgumentException If the repository contract already exists.
     */
    protected function ensureRepositoryContractDoesNotAlreadyExist($model)
    {
        $classFullyQualified = $this->getRepositoryContractNamespace($model);
        if (class_exists($classFullyQualified, false)) {
            throw new InvalidArgumentException("{$classFullyQualified} already exists.");
        }
    }

    /**
     * Get the model's fully qualified class name.
     *
     * @param string $model The model name.
     * @return string The model's fully qualified class name.
     */
    protected function getModelNamespace($model)
    {
        return config('repository_pattern_generator.base_application_namespace') . '\\' . config('repository_pattern_generator.model_base_path') . '\\' . $this->getClassName($model);
    }

    /**
     * Get the repository's fully qualified class name.
     *
     * @param string $model The model name.
     * @return string The repository's fully qualified class name.
     */
    protected function getRepositoryNamespace($model)
    {
        return config('repository_pattern_generator.base_application_namespace') . '\\' . config('repository_pattern_generator.repositories_base_namespace') . '\\' . $this->getRepositoryClassName($model);
    }

    /**
     * Get the repository contract's fully qualified class name.
     *
     * @param string $model The model name.
     * @return string The repository contract's fully qualified class name.
     */
    protected function getRepositoryContractNamespace($model)
    {
        return config('repository_pattern_generator.base_application_namespace') . '\\' . config('repository_pattern_generator.repository_contract_base_namespace') . '\\' . $this->getRepositoryContractClassName($model);
    }

    /**
     * Return the repository base namespace.
     *
     * @return string The repository base namespace.
     */
    protected function getRepositoryBaseNamespace()
    {
        return config('repository_pattern_generator.base_application_namespace') . '\\' . config('repository_pattern_generator.repositories_base_namespace');
    }

    /**
     * Return the repository contract base namespace.
     *
     * @return string The repository contract base namespace.
     */
    protected function getRepositoryContractBaseNamespace()
    {
        return config('repository_pattern_generator.base_application_namespace') . '\\' . config('repository_pattern_generator.repository_contract_base_namespace');
    }

    /**
     * Get the filesystem instance.
     *
     * @return Filesystem The filesystem instance.
     */
    public function getFilesystem()
    {
        return $this->_filesystem;
    }
}
