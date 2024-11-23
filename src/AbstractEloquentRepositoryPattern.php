<?php

namespace phpsamurai\RepositoryPatternGenerator;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Log;

abstract class AbstractEloquentRepositoryPattern
{
    /**
     * Model instance.
     *
     * @var Model
     */
    protected Model $_model;

    /**
     * Return all instances of the model, with optional relationships.
     *
     * @param array $with Relationships to eager load with the model instances.
     * @return Collection|null All instances of the model, or null on failure.
     */
    public function all(array $with = [])
    {
        try {
            return $this->_model->with($with)->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return null;
        }
    }

    /**
     * Returns the $model instance having the given $id.
     *
     * @param mixed $id The identifier of the model instance.
     * @param array $with Relationships to eager load.
     * @param bool $fail Whether to throw an exception if no model is found.
     * @return Model|Collection|null The found model or null.
     */
    public function findById($id, array $with = [], $fail = true)
    {
        $q = $this->_model->with($with);
        return ($fail) ? $q->findOrFail($id) : $q->find($id);
    }

    /**
     * Returns the $model having $key equals to $value.
     *
     * @param string $key The column key to search by.
     * @param mixed $value The value to match the column key against.
     * @param array $with Relationships to eager load.
     * @param string $comparator The comparison operator to use.
     * @param bool $fail Whether to throw an exception if no model is found.
     * @return Model|Collection|null The found model or null.
     */
    public function getFirstBy(string $key, $value, array $with = [], string $comparator = '=', bool $fail = false)
    {
        $q = $this->_model->with($with)->where($key, $comparator, $value);
        return $fail ? $q->firstOrFail() : $q->first();
    }

    /**
     * Get paginated models.
     *
     * @param int $page The current page number.
     * @param int $limit The number of items per page.
     * @param array $with Relationships to load with the models.
     * @return \stdClass An object containing paginated items and total item count.
     */
    public function getByPage($page = 1, $limit = 10, array $with = [])
    {
        $result = new \stdClass();
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = collect([]);

        $query = $this->_model->with($with);

        $items = $query->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        $result->totalItems = $query->count();
        $result->items = $items;

        return $result;
    }

    /**
     * Create a new model instance.
     *
     * @param array $data
     * @return Model The newly created model instance.
     */
    public function create(array $data)
    {
        return $this->_model->create($data);
    }

    /**
     * Update the specified model with the provided data.
     *
     * @param Model $model The model to update.
     * @param array $data The data to update the model with.
     * @return Model|false The updated model or false on failure.
     * @throws InvalidArgumentException If the provided model is not an instance of the repository class.
     */
    public function update($model, array $data)
    {
        $repositoryClass = get_class($this->_model);

        if (!($model instanceof $repositoryClass)) {
            throw new InvalidArgumentException("The model is not an instance of {$repositoryClass}.");
        }

        if ($model->update($data)) {
            return $model;
        }

        return false;
    }

    /**
     * Delete a model instance.
     *
     * @param mixed $model The model instance to be deleted.
     * @return bool Indicates whether the model was successfully deleted.
     * @throws InvalidArgumentException If the provided model is not an instance of the repository class.
     */
    public function delete($model)
    {
        $repositoryClass = get_class($this->_model);

        if (!($model instanceof $repositoryClass)) {
            throw new InvalidArgumentException("The model is not an instance of {$repositoryClass}.");
        }

        return $model->delete();
    }

    /**
     * Truncate all records from the model's table.
     *
     * @return void
     */
    public function truncate()
    {
        $this->_model->truncate();
    }
}
