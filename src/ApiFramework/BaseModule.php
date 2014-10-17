<?php

namespace ApiFramework;

class BaseModule extends Core
{

    /**
     * @var string Table name
     */
    protected $table = null;

    /**
     * @var string Primary key column name
     */
    protected $primaryKey = 'id';

    /**
     * @var boolean Pagination indicator
     */
    protected $paginate = true;

    /**
     * @var array Default pagination settings
     */
    protected $paging = [
        'records' => 0,
        'offset'  => 0,
        'limit'   => 50
    ];

    /**
     * @var Database Database object
     */
    protected $db;

    /**
     * @var array Module properties
     */
    protected $data = [];

    /**
     * @var array Where filters
     */
    protected $where = [];

    /**
     * @var array Order filters
     */
    protected $order = [];

    /**
     * @var array Group filters
     */
    protected $group = [];

    /**
     * @var array One to many relationships
     */
    protected $joins = [];

    /**
     * @var array Many to many relationships
     */
    protected $with = [];

    /**
     * @var array Fields to be retrieved
     */
    protected $fields = [];

    /**
     * @var array Fields to be objectified
     */
    protected $objectify = [];

    /**
     * @var array Fields to filter by
     */
    protected $validFilters = [];

    /**
     * @var array Writable fields
     */
    protected $writable = [];

    /**
     * @var array Object settings
     */
    protected $settings = [
        'fields',
        'joins',
        'with',
        'group',
        'order',
        'where',
        'objectify',
        'data'
    ];

    /**
     * @var array Default settings
     */
    protected $defaults = [];

    /**
     * @var array Query debug
     */
    protected $debugQueries = false;


    /**
     * Class constructor
     *
     * @param App Application instance
     */
    function __construct (App $app) {
        parent::__construct($app);

        // Database connection shortcut
        $this->db = $this->app->db;

        // Save original settings
        foreach ($this->settings as $setting) {
            $this->defaults[$setting] = $this->{$setting};
        }

        // Set debug
        $this->debugQueries = $this->app->config('debug.queries');
    }


    /**
     * Set offset for next queries
     * 
     * @param integer $offset Number of elements to offset
     * @return object $this Module instance
     */
    function offset ($offset = null) {
        if (isset($offset)) {
            $this->paging['offset'] = (int) $offset;
        }
        return $this;
    }


    /**
     * Set limit for next queries
     * 
     * @param integer $limit Number of elements to retrieve
     * @return object $this Module instance
     */
    function limit ($limit = null) {
        if (isset($limit)) {
            $this->paging['limit'] = (int) $limit;
        }
        return $this;
    }


    /**
     * Set order by for next queries
     * 
     * @param string $order Field to order by
     * @return object $this Module instance
     */
    function order ($order = null) {
        if (isset($order)) {
            $this->order = $order;
        }
        return $this;
    }


    /**
     * Set group by for next queries
     * 
     * @param integer $group Field to group by
     * @return object $this Module instance
     */
    function group ($group = null) {
        if (isset($group)) {
            $this->group = $group;
        }
        return $this;
    }


    /**
     * Set objectify fields by for next queries
     * 
     * @param array $fields Fields to objectify
     * @return object $this Module instance
     */
    function objectify ($fields) {
        $this->objectify = $fields;
        return $this;
    }


    /**
     * List resource collection
     * 
     * @return array $collection
     */
    function index () {

        $response = [];

        if (!empty($this->where)) {
            $where = $this->where;
        }

        // Add pagination to query
        if ($this->paginate) {
            $where['LIMIT'] = [(int)$this->paging['offset'], (int)$this->paging['limit']];
        }

        // Add Order By
        if (!empty($this->order)) {
            $where['ORDER'] = $this->order;
        }

        // Fields to retrieve
        $fields = ($this->fields) ? $this->fields : $this->table . ".*";

        // Process with tables (many-to-many relations)
        if ($this->with) {
            foreach ($this->with as $_with => $with) {

                // Add table to joins
                $this->joins['[>]'.$with[0]] = [$this->primaryKey => $with[1]];

                $_field .= "GROUP_CONCAT(".$with[0].".".$with[2].")(_group_".$_with.")";

                // Add field retrieve
                if (is_array($fields)) {
                    $fields[] = $_field;
                } else {
                    $fields .= ', '.$_field;
                }

                // Add objectify for this element
                $this->objectify[] = '_group_'.$_with;
            }
            // Add group by primary key
            $this->group($this->table.'.'.$this->primaryKey);
        }

        // Add Group By
        if ($this->group) {
            $where['GROUP'] = $this->group;
        }

        // Retrieve results from database
        if ($this->joins) {
            $collection = $this->db->select($this->table, $this->joins, $fields, $where);
//echo '<pre>'.print_r($this->joins, true).'</pre>';
//echo '<pre>'.print_r($where, true).'</pre>';
//echo $this->db->last_query();
//echo "\n<br/>Error: ".print_r($this->db->error(), true)."\n<br>";

            // Debug
            if ($this->debugQueries) {
                $response['debug']['query'] = $this->db->last_query();
            }

            // Get total records
            $records = (int)$this->db->count($this->table, $this->joins, "*", $this->where);
        } else {
            $collection = $this->db->select($this->table, $fields, $where);
//echo '<pre>'.print_r($where, true).'</pre>';
//echo $this->db->last_query();
//echo "\n<br/>Error: ".print_r($this->db->error(), true)."\n<br>";

            // Debug
            if ($this->debugQueries) {
                $response['debug']['query'] = $this->db->last_query();
            }

            // Get total records
            $records = (int)$this->db->count($this->table, "*", $this->where);
        }

        // Process with fields
        if ($this->with) {
            foreach ($collection as $k => $v) {
                foreach ($this->with as $_with => $with) {
                    $collection[$k][$_with] = explode(',',$collection[$k]['_group_'.$_with]);
                    unset($collection[$k]['_group_'.$_with]);
                }
            }
        }

        // Objectify response
        if ($this->objectify) {
            $collection = $this->app->response->objectify($collection, $this->objectify);
        }

        $response['success'] = (bool) $collection;
        // Paginate results
        if ($this->paginate) {
            $this->paging['records'] = (int)$records;
            $response['paging'] = $this->paging;
        }
        $response['data'] = $collection ? : [];

        $this->resetSettings();

        return $response;
    }


    /**
     * Creates new element
     * 
     * @param array $data Key value array with information to store
     * @return $id Last id created or false
     */
    function create ($data = null) {
        if (isset($data)) {
            $this->data = array_merge($this->data, $data);
        }
        $id = $this->db->insert($this->table, $this->data);
        if ($this->debugQueries) {
            $response['debug']['query'] = $this->db->last_query();
        }
        $response['success'] = (bool) $id;
        $response['data'] = ['id' => $id];
        return $response;
    }


    /**
     * Update existing element
     * 
     * @param array $data Key value array with information to update
     * @param array $where Condition to filter records tu update
     * @return boolean Success or fail of update
     */
    function update ($id, $data = null) {
        if (isset($data)) {
            $this->data = array_merge($this->data, $data);
        }
        $updated = (bool) $this->db->update($this->table, $this->data, [$this->primaryKey => $id]);
        if ($this->debugQueries) {
            $response['debug']['query'] = $this->db->last_query();
        }
        $response['success'] = $updated;
        $response['data'] = ['id' => $id];
        return $response;
    }


    /**
     * Delete existing element from resource collection
     * 
     * @param string $id Element id to delete
     * @return boolean
     */
    function destroy ($id) {
        $destroyed = (bool) $this->db->delete($this->table, [$this->primaryKey => $id]);
        if ($this->debugQueries) {
            $response['debug']['query'] = $this->db->last_query();
        }
        $response['success'] = $destroyed;
        $response['data'] = ['id' => $id];
        return $response;
    }


    /**
     * Retrieves a single element from the collection
     * 
     * @param string $id Element ID
     * @return boolean
     */
    function show ($id) {
        $this->paginate = false;
        $this->where($this->table . '.' . $this->primaryKey, $id);
        return $this->first();
    }


    /**
     * Retrieves the first element of the collection
     * 
     * @return boolean
     */
    function first () {
        $this->paginate = false;
        $response = $this->index();
        $response['data'] = current($response['data']);
        return $response;
    }


    /**
     * Add filter to apply in index method
     * 
     * @param string|array $field Field to filter for or array with multiple filters.
     * @param string $value Filter value
     * @return object
     */
    public function where ($field, $value = null) {
        return $this->filter($this->where["AND"], $this->validFilters, $field, $value);
    }


    /**
     * Add data for crud methods
     * 
     * @param string|array $field Field to filter for or array with multiple filters.
     * @param string $value Filter value
     * @return object
     */
    public function data ($field, $value = null) {
        return $this->filter($this->data, $this->writable, $field, $value);
    }


    /**
     * Stores valid values for given array
     * 
     * @param array $store Array to store values 
     * @param array $valids Array 
     * @param string|array $field Key to store for or array with multiple filters.
     * @param string $value Key value
     * @return object
     */
    private function filter (&$store, $valids, $field, $value = null) {
        $_valids = [$this->primaryKey, $this->table . '.' . $this->primaryKey];
        $valids = array_merge($valids, $_valids);
        if (!is_array($field) && !$value) {
            return false;
        }
        if (is_array($field)) {
            foreach ($field as $k => $v) {
                $this->filter($store, $valids, $k, $v);
            }
        } else {
            if (in_array($field, $valids) || isset($valids[$field])) {
                if (isset($valids[$field])) {
                    $field = $valids[$field];
                }
                if (preg_match('/^\*|\*$/is', $field)) {
                    $store['LIKE'][preg_replace('/\*/is', '%', $field)] = $value;
                } else {
                    $store[$field] = $value;
                }
            }
        }
        return $this;
    }


    /**
     * Revert to the default settings
     * 
     * @return object
     */
    private function resetSettings () {
        foreach ($this->settings as $setting) {
            $this->{$setting} = $this->defaults[$setting];
        }
    }


    /**
     * Add new field to retrieve
     * 
     * @param string|array $field Field to retrieve for or array with multiple fields
     * @return object|mixed
     */
    function field ($field = null) {
        if (!$field) {
            return (!is_array($this->fields) || count($this->fields) <= 1) ? (string) $this->fields : $this->fields;
        }
        if (is_array($field)) {
            foreach ($field as $k => $v) {
                $this->field($v);
            }
        } else {
            if (in_array($field, $this->fields)) {
                unset($this->fields[array_search($field, $this->fields)]);
            }
            if (in_array(str_replace($this->table . '.', '', $field), $this->fields)) {
                unset($this->fields[array_search(str_replace($this->table . '.', '', $field), $this->fields)]);
            }
            $this->fields[] = $field;
        }
        return $this;
    }


    /**
     * Add new table to join
     * 
     * @param string|array $table Table to join
     * @param string $lKey Left key to join tables
     * @param string $rKey Right key to join tables. If not defined, uses the same as $lKey
     * @return object
     */
    function join ($table, $lKey, $rKey = null) {
        if (!is_array($table) && !$lKey) {
            return false;
        }
        $this->joins[$table] = ($rKey)? [$lKey => $rKey] : $lKey;
        return $this;
    }


    /**
     * Retrieve results quantity
     * 
     * @return integer
     */
    public function records () {
        return $this->paging['records'];
    }


    /**
     * Retrieve total pages quantity
     * 
     * @return integer Pages count
     */
    public function pages () {
        if ($this->paging['limit'] == 0) {
            return 0;
        }
        return ceil($this->paging['records'] / $this->paging['limit']);
    }


    /**
     * Retrieve primary key for this module
     * 
     * @return string Primary key
     */
    public function primaryKey () {
        return $this->primaryKey;
    }


    /**
     * Retrieve valid filters for this module
     * 
     * @return array Valid filters
     */
    public function validFilters () {
        return $this->validFilters;
    }


    /**
     * Retrieve writable fields for this module
     * 
     * @return array Writable fields
     */
    public function writableFields () {
        return $this->writable;
    }


}