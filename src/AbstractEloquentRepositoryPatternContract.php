<?php

namespace phpsamurai\RepositoryPatternGenerator;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use StdClass;

interface AbstractEloquentRepositoryPatternContract
{

    /**
     * Return all instances of the model.
     *
     * @param array $with Relationships to eager load.
     * @return Collection|Model[] A collection of model instances.
     */
    public function all(array $with = []);

    /**
     * Returns the model instance having the given ID.
     *
     * @param int|string $id The ID of the model.
     * @param array $with Relationships to eager load.
     * @param bool $fail Whether to throw an exception if the model is not found.
     * @return Collection|Model|null The model instance or null if not found.
     */
    public function findById($id, array $with = [], $fail = true);

    /**
     * Returns the first model having the key equals to the value.
     *
     * @param string $key The field name.
     * @param mixed $value The value to search for.
     * @param array $with Relationships to eager load.
     * @param string $comparator The comparison operator.
     * @param bool $fail Whether to throw an exception if the model is not found.
     * @return Model|null The model instance or null if not found.
     */
    public function getFirstBy($key, $value, array $with = [], $comparator = '=', $fail = false);

    /**
     * Get paginated models.
     *
     * @param int $page The current page number.
     * @param int $limit The number of models per page.
     * @param array $with Relationships to eager load.
     * @return StdClass An object with items and totalCount for pagination.
     */
    public function getByPage($page = 1, $limit = 10, array $with = []);

    /**
     * Creates a new model instance.
     *
     * @param array $data The data to fill the model.
     * @return Model The newly created model instance.
     */
    public function create(array $data);

    /**
     * Updates the model having the given id.
     *
     * @param Model $model The model instance to update.
     * @param array $data The data to update the model with.
     * @return Model|bool The updated model instance or false on failure.
     * @throws InvalidArgumentException
     */
    public function update($model, array $data);

    /**
     * Deletes the model having the given id.
     *
     * @param Model $model The model instance to delete.
     * @return bool True if deletion was successful, false otherwise.
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function delete($model);

    /**
     * Truncates the model's table.
     *
     * @return void
     */
    public function truncate();
}
