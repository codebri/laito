<?php namespace ApiFramework;

/**
 * Model class
 *
 * @package default
 * @author Mangolabs
 */

class Model extends Core {

    /**
     * @var object Database instance
     */
    protected $db;

    /**
     * @var string Table name
     */
    protected $table = '';

    /**
     * @var string Records offset
     */
    protected $offset = 0;

    /**
     * @var string Records limit
     */
    protected $limit = 100;

    /**
     * @var string Records collection
     */
    protected $records;

    /**
     * @var string Primary key
     */
    protected $primaryKey = 'id';

    /**
     * @var array Columns to retrieve
     */
    protected $columns = [];

    /**
     * @var array Applicable filters
     */
    protected $filters = [];

    /**
     * @var array Fields that could be written
     */
    protected $fillable = [];

    /**
     * @var array Fields that have to be validated before writing
     */
    protected $validate = [];

    /**
     * @var array Model relationships declaration
     */
    protected $relationships = [
        'hasOne' => [],
        'hasMany' => [],
        'belongsToMany' => []
    ];

    /**
     * @var array Default validation rules
     */
    protected $rules = [];

    /**
     * @var array Validation rules
     */
    protected $defaultRules = [
        'alpha' => '/[a-zA-Z\s]+/',
        'numeric' => '/[0-9]+/',
        'alphanumeric' => '/[-\w\s]+/',
        'email' => '/[-\w]+(\.-\w+)*@[-\w]+(\.[-\w]+)*(\.[a-zA-Z]{2,6})/'
    ];

    /**
     * @var int Number of retrieved models
     */
    protected $count;

    /**
     * Class constructor
     *
     * @param App $app App instance
     */
    public function __construct (App $app) {

        // Construct from parent
        parent::__construct($app);

        // Setup database
        $this->db = $this->app->db;

        // Set table name and columns to select
        $this->db->table($this->table)->select($this->columns);

        // Merge validation rules
        $this->rules = array_merge($this->defaultRules, $this->rules);
    }

    /**
     * Sets the offset
     *
     * @param int $offset Offset number
     * @return object Model instance
     */
    public function offset ($offset) {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Sets the limit
     *
     * @param int $limit Limit number
     * @return object Model instance
     */
    public function limit ($limit) {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Sets the columns to be selected
     *
     * @param array $columns Columns to retrieve
     * @return object Model instance
     */
    public function columns ($columns) {
        if (is_array($columns)) {
            $this->columns = $columns;
        }
        return $this;
    }

    /**
     * Sets a where condition
     *
     * @param string $column Column name
     * @param string $value Value to match
     * @param string $operator Operator to compare with
     * @return object Database instance
     */
    public function where ($column, $value, $operator = '=') {
        $this->db->where($column, $value, $operator);
        return $this;
    }

    /**
     * Sets an array of filters as where conditions
     *
     * @param array $params Search parameters
     * @return bool|array Array of results, or false
     */
    function search ($filters = []) {

        // Filters has to be an array
        if (!is_array($filters)) {
            return false;
        }

        // Set limit and offset
        if (isset($filters['limit'])) {
            $this->limit($filters['limit']);
        }
        if (isset($filters['offset'])) {
            $this->offset($filters['offset']);
        }

        // Remove invalid filters
        $filters = array_intersect_key($filters, array_flip(array_keys($this->filters)));

        // Set where conditions
        foreach ($filters as $key => $value) {
            $current = $this->filters[$key];
            $column = $current[0];
            $operator = isset($current[1])? $current[1] : '=';
            $this->where($column, $value, $operator);
        }

        // Return model instance
        return $this;
    }

    /**
     * Returns an array of models
     *
     * @return bool|array Array of models, or false
     */
    function get () {

        // Set basic query
        $this->db->table($this->table)->limit($this->limit)->offset($this->offset);

        // Set columns to select
        $this->db->select($this->columns);

        // Resolve relationships
        $this->hasOne()->belongsToMany();

        // Get results
        $this->records = $this->db->get();

        // Get results count
        $this->count = $this->db->count();

        // Add has many relationships
        $this->hasMany();

        // Return query results
        return $this->records;
    }

    /**
     * Returns the total number of models
     *
     * @return int Total number of models
     */
    function count () {
        return $this->count;
    }

    /**
     * Returns a single model by primary key
     *
     * @param string $value Primary key value
     * @return bool|array Model attributes, or false
     */
    function find ($value) {

        // Value has to be defined
        if (!isset($value)) {
            return false;
        }

        // Use the primary key for the where
        $this->db->table($this->table)->where($this->primaryKey, $value, '=', $this->table);

        // Return the first found record
        $result = $this->get();

        // Return the first matching record, or false if no records were found
        return is_array($result) ? reset($result) : false;
    }

    /**
     * Returns the first model found
     *
     * @return bool|array Model attributes, or false
     */
    function first () {

        // Perform query
        $result = $this->get();

        // Return the first matching model, or false if no models were found
        return is_array($result) ? reset($result) : false;
    }

    /**
     * Creates a new model
     *
     * @param $attributes Model attributes
     * @return bool|array Created model ID, or false
     */
    function create ($attributes) {

        // Attributes has to be array
        if (!(isset($attributes)) || !is_array($attributes)) {
            return false;
        }

        // Remove non fillable attributes
        $fields = array_intersect_key($attributes, array_flip($this->fillable));

        // Validate attributes
        if ($this->validationErrors($fields)) {
            return false;
        }

        // Create the model and return its ID
        $result = $this->db->table($this->table)->insertGetId($fields);

        // Return false if create fails
        if (!$result) {
            return false;
        }

        // Sync many to many relationships
        $this->sync($result, $attributes);

        // Upsert has many relationships
        $this->updateHasMany($result, $attributes);

        // Return the created model
        return $this->find($result);
    }

    /**
     * Updates a model
     *
     * @param $id Model primary key
     * @param $attributes Model attributes
     * @return bool|array Updated model, or false
     */
    function update ($id, $attributes) {

        // ID has to be defined, attributes an array
        if (!isset($id) || !isset($attributes) || !is_array($attributes)) {
            return false;
        }

        // Remove non fillable attributes
        $fields = array_intersect_key($attributes, array_flip($this->fillable));

        // Validate attributes
        if ($this->validationErrors($fields)) {
            return false;
        }

        // Update the model
        $result = $this->db->table($this->table)->where($this->primaryKey, $id, '=', $this->table)->update($fields);

        // Return false if the update failed
        if (!$result) {
            return false;
        }

        // Sync many to many relationships
        $this->sync($id, $attributes);

        // Upsert has many relationships
        $this->updateHasMany($id, $attributes);

        // Return the updated model
        return $this->find($id);
    }

    /**
     * Destroys a model
     *
     * @return bool|array Deleted model ID, or false
     */
    function destroy ($id) {

        // ID has to be defined
        if (!isset($id)) {
            return false;
        }

        // Update the model
        $result = $this->db->table($this->table)->where($this->primaryKey, $id)->limit(1)->delete();

        // Return the destroyed model ID, or false on fail
        return ($result)? $id : false;
    }

    /**
     * Resolves one to one relationships
     *
     * @return object Model instance
     */
    private function hasOne () {

        // Return if there are no relationships of that type
        if (!isset($this->relationships['hasOne']) || empty($this->relationships['hasOne'])) {
            return $this;
        }

        // Iterate relationships
        foreach ($this->relationships['hasOne'] as $join) {

            // Perform join
            $this->db->join($join['table'], $join['localKey'], '=', $join['foreignKey']);

            // Add related columns to select
            foreach ($join['columns'] as $column) {
                $column = $join['table'] . '.' . $column . ' as ' . $join['alias'] . '_' . $column;
                $this->db->addSelect($column);
            }
        }

        // Return model instance
        return $this;
    }

    /**
     * Resolves one to many relationships
     *
     * @return object Model instance
     */
    private function hasMany () {

        // Return if there are no relationships of that type
        if (!isset($this->relationships['hasMany']) || empty($this->relationships['hasMany'])) {
            return $this;
        }

        // Get current records IDs
        $ids = array_column($this->records, 'id');

        // Make a associative array from current records
        $records = array_combine($ids, $this->records);

        // Iterate relationships
        foreach ($this->relationships['hasMany'] as $join) {

            // Get relationship results
            $childs = $this->db->table($join['table'])->select(array_merge([$join['foreignKey']], $join['columns']))->whereIn($join['foreignKey'], $ids)->limit($join['limit'])->get();

            // Group them by foreign key
            if ($childs) {
                foreach ($childs as $child) {
                    $key = $join['foreignKey'];
                    $id = $child[$key];

                    // If the ID was not required as a column to show, leave it out
                    if (!in_array($key, $join['columns'])) {
                        unset($child[$key]);
                    }

                    // Save the result
                    $join['results'][$id][] = $child;
                }
            }

            // Add relationship results to the main collection
            foreach ($records as $key => $value) {
                $id = $value[$join['localKey']];
                $records[$id][$join['alias']] = (isset($join['results'][$id]))? $join['results'][$value[$join['localKey']]] : [];
            }
        }

        // Save the records with the resolved relationships
        $this->records = array_values($records);

        // Return model instance
        return $this;
    }

    /**
     * Resolves many to many relationships
     *
     * @return object Model instance
     */
    private function belongsToMany () {

        // Return if there are no relationships of that type
        if (!isset($this->relationships['belongsToMany']) || empty($this->relationships['belongsToMany'])) {
            return $this;
        }

        // Group by primary key
        $this->db->groupBy($this->table . '.' . $this->primaryKey);

        // Iterate relationships
        foreach ($this->relationships['belongsToMany'] as $join) {

            // Perform join
            $this->db->join($join['pivot'], $this->primaryKey, '=', $join['localKey']);

            // Add related columns to select
            $column = 'GROUP_CONCAT(' . $join['pivot'] . '.' . $join['foreignKey'] . ') as concat_' . $join['alias'];
            $this->db->addSelect($column);
        }

        // Return model instance
        return $this;
    }

    /**
     * Syncs many to many relationships
     *
     * @param $attributes Model attributes
     * @return object Model instance
     */
    private function sync ($id, $attributes) {

        // Return if the ID is invalid, or attributes is not an array
        if (!isset($id) || !isset($attributes) || empty($attributes)) {
            return $this;
        }

        // Return if there are no relationships of that type
        if (!isset($this->relationships['belongsToMany']) || empty($this->relationships['belongsToMany'])) {
            return $this;
        }

        // Sync each relationship
        foreach ($this->relationships['belongsToMany'] as $join) {

            // The sync option has to be true, the related attribute an array
            if (isset($join['sync']) && $join['sync'] && isset($attributes[$join['alias']]) && is_array($attributes[$join['alias']])) {

                // Delete old values
                $delete = $this->db->table($join['pivot'])->where($join['localKey'], $id, '=')->delete();

                // Insert new values
                foreach ($attributes[$join['alias']] as $value) {
                    $this->db->table($join['pivot'])->insert([$join['localKey'] => $id, $join['foreignKey'] => $value]);
                }
            }
        }

        // Return model instance
        return $this;
    }

    /**
     * Upserts one to many relationships
     *
     * @param $attributes Model attributes
     * @return object Model instance
     */
    private function updateHasMany ($id, $attributes) {

        // Return if the ID is invalid, or attributes is not an array
        if (!isset($id) || !isset($attributes) || empty($attributes)) {
            return $this;
        }

        // Return if there are no relationships of that type
        if (!isset($this->relationships['hasMany']) || empty($this->relationships['hasMany'])) {
            return $this;
        }

        // Sync each relationship
        foreach ($this->relationships['hasMany'] as $join) {

            // The sync option has to be true, the related attribute an array
            if (isset($join['sync']) && is_array($join['sync']) && isset($attributes[$join['alias']]) && is_array($attributes[$join['alias']])) {

                // Get related elements
                $related = $attributes[$join['alias']];

                // Delete elements if the overwrite option was set
                if (in_array('overwrite', $join['sync'])) {
                    $results = $this->db->table($join['table'])->where($join['foreignKey'], $id)->limit(100)->get();
                    if ($results) {
                        $ids = array_diff(array_column($results, 'id'), array_column($related, 'id'));
                        $this->db->table($join['table'])->whereIn('id', $ids)->delete();
                    }
                }

                // Iterate related elements
                foreach ($related as $value) {

                    // Leave only writable fields
                    $fields = array_intersect_key($value, array_flip($join['columns']));

                    // Add the foreign key
                    $fields[$join['foreignKey']] = $id;

                    // Insert elements whitout ID
                    if (in_array('insert', $join['sync']) && !isset($value['id'])) {
                        $this->db->table($join['table'])->insert($fields);
                    }

                    // Update elements width ID
                    if (in_array('update', $join['sync']) && isset($value['id'])) {

                        // Check if the existing element belongs to this record
                        $current = $this->db->table($join['table'])->where('id', $value['id'])->limit(1)->getOne();
                        if ($current && isset($current[$join['foreignKey']]) && $current[$join['foreignKey']] == $id) {
                            $this->db->table($join['table'])->where('id', $value['id'])->update($fields);
                        }
                    }
                }
            }
        }

        // Return model instance
        return $this;
    }

    /**
     * Validates a model
     *
     * @param $attributes Attributes to validate
     * @return array|bool Array of errors, or false if the model is valid
     */
    function validationErrors ($attributes) {

        // Ruleset
        $ruleSet = $this->validate;

        // Errors collector
        $errors = [];

        // Leave only the validable attributes
        $attributes = array_intersect_key($attributes, array_flip(array_keys($ruleSet)));

        // Check for required attributes
        foreach ($ruleSet as $key => $rules) {
            if (in_array('required', $rules) && !isset($attributes[$key])) {
                $errors[] = ['required', $key, ''];
            }
            $ruleSet[$key] = array_diff($rules, ['required']);
        }

        // Check for format errors
        foreach ($attributes as $key => $value) {
            foreach ($ruleSet[$key] as $name => $rule) {
                if (!preg_match($this->rules[$rule], $value)) {
                    $errors[] = [$rule, $key, $value];
                }
            }
        }

        // Return errors or false
        return count($errors)? $errors : false;
    }

    /**
     * Returns the limit and offset options
     *
     * @param int $limit Limit number
     * @return object Model instance
     */
    public function pagination () {
        return ['offset' => $this->offset, 'limit' => $this->limit];
    }

}