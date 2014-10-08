<?php

namespace ApiFramework;

/**
 * BaseModule
 *
 * Basic starting module
 * @version 1.0
 * @package BaseModule
*/
class BaseModule extends Core
{

    protected $db; // Objeto de base de datos
    private $_error = '';
    private $_debug = [];
    private $_db_debug = false;
    private $cache_ttl = 172800; // Standard time for cache ttl in seconds (2 hours)

    protected $data = [];
    protected $where = [];
    protected $objectify = [];
    protected $fields = null;
    protected $joins = null;
    protected $group = null;
    protected $order = null;

    // Protected variables for overloading
    protected $table = null;
    protected $primaryKey = 'id';
    protected $paging = [
        'records' => 0,
        'limit' => 50,
        'offset' => 0,
    ];
    protected $paginate = true; // Pagination activated
    protected $cache = true; // Returns cache if exists
    protected $validFilters = []; // Valid fields to filter for
    protected $writable = ['aaa', 'ccc']; // Valid fields for POST/PUT

    /**
     * Class constructor
     * 
     */
    function __construct ()
    {
        // Database connection
        $this->db = new \medoo([
            'database_type' => 'mysql',
            'database_name' => CONFIG_DB_NAME,
            'server' => CONFIG_DB_HOST,
            'username' => CONFIG_DB_USER,
            'password' => CONFIG_DB_PASSWORD
        ]);
    }

    /**
     * Set offset for next queries
     * 
     * @param integer $offset
     * @return object $this
     */
    function offset ($offset)
    {
        $this->paging['offset'] = (int)$offset;
        return $this;
    }

    /**
     * Set limit for next queries
     * 
     * @param integer $limit
     * @return object $this
     */
    function limit ($limit)
    {
        $this->paging['limit'] = (int)$limit;
        return $this;
    }

    /**
     * Set order by for next queries
     * 
     * @param string $field Field to order by 
     * @return object $this
     */
    function order ($field)
    {
        $this->order = $field;
        return $this;
    }

    /**
     * Set group by for next queries
     * 
     * @param integer $field
     * @return object $this
     */
    function group ($field)
    {
        $this->group = $field;
        return $this;
    }

    /**
     * List resource collection
     * 
     * @return array $collection
     */
    function index ()
    {
        // Get input parameters
        $input = Request::input();
        // Apply filters
        $this->where($input);

        $where = $this->where;
        if ($this->paginate) {
            // Set pagination from parameters
            $this->paging['limit'] = (int)(isset($input['limit'])? $input['limit'] : $this->paging['limit']);
            $this->paging['offset'] = (int)(isset($input['offset'])? $input['limit'] : $this->paging['offset']);

            // Add pagination to query
            $where['LIMIT'] = [(int)$this->paging['offset'], (int)$this->paging['limit']];
        }

        // Add Order By
        if ($this->order) {
            $where['ORDER'] = $this->order;
        }

        // Add Group By
        if ($this->group) {
            $where['GROUP'] = $this->group;
        }

        // Fields to retrieve
        $fields = ($this->fields)? $this->fields : "*";

        // Retrieve results from database
        if ($this->joins) {
            $collection = $this->db->select($this->table, $this->joins, $fields, $where);
// echo '<pre>'.print_r($this->joins, true).'</pre>';
// echo '<pre>'.print_r($where, true).'</pre>';
// echo $this->db->last_query();
// echo "\n<br/>Error: ".print_r($this->db->error(), true)."\n<br>";
            // Get total records
            $records = (int)$this->db->count($this->table, $this->joins, "*", $this->where);
        } else {
            $collection = $this->db->select($this->table, $fields, $where);
// echo '<pre>'.print_r($where, true).'</pre>';
// echo $this->db->last_query();
// echo "\n<br/>Error: ".print_r($this->db->error(), true)."\n<br>";
            // Get total records
            $records = (int)$this->db->count($this->table, "*", $this->where);
        }

        // Paginate results
        $this->paging['records'] = (int)$records;
        Response::metadata('paging', $this->paging);

        // Objectify response
        if ($this->objectify)
            $collection = \ApiFramework\Response::objectify($collection, $this->objectify);

        return $collection;
    }

    /**
     * Create new element for resource collection
     * 
     * @param array $data Key value array with information to store
     * @return $last_id Last id created or false
     */
    function create ($data=[])
    {
        $data = $this->data;

        // Create new row in database
        $last_id = $this->db->insert($this->table, $data);

        // Empty $this->data
        $this->data = [];
        return $last_id;
    }

    /**
     * Update existing element from resource collection
     * 
     * @param array $data Key value array with information to update
     * @param array $where Condition to filter records tu update
     * @return boolean
     */
    function update ($id)
    {
        $data = $this->data;

        $where = [$this->primaryKey => $id];

        // Update existing row in database
        $affected = $this->db->update($this->table, $data, $where);

        return (bool)$affected;
    }

    /**
     * Delete existing element from resource collection
     * 
     * @param string $id Element id to delete
     * @return boolean
     */
    function destroy ($id)
    {
        // Update existing row in database
        $affected = $this->db->delete($this->table, [$this->primaryKey => $id]);

        return (bool)$affected;
    }

    /**
     * Retrieve element details from resource collection
     * 
     * @param string $id Element id to retrieve
     * @return boolean
     */
    function show ($id)
    {
        // Update existing row in database
        $element = $this->db->get($this->table, "*", [$this->primaryKey => $id]);

        return $element;
    }

    /**
     * Add filter to apply in index method
     * 
     * @param string|array $field Field to filter for or array with multiple filters.
     * @param string $value Filter value
     * @return object
     */
    public function where ($field, $value=null)
    {
        return $this->filter($this->where, $this->validFilters, $field, $value);
    }

    /**
     * Add data for crud methods
     * 
     * @param string|array $field Field to filter for or array with multiple filters.
     * @param string $value Filter value
     * @return object
     */
    public function data ($field, $value=null)
    {
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
    private function filter (&$store, $valids, $field, $value=null)
    {
        if (!is_array($field) && !$value) {
            return false;
        }

        if (is_array($field)) {
            foreach ($field as $k => $v) {
                $this->filter($store, $valids, $k, $v);
            }
        } else {
            if (in_array($field, $valids) || isset($valids[$field])) {
                if ($valids[$field]) {
                    $field = $valids[$field];
                }

                // Full text search
                if (preg_match('/^\*|\*$/is', $field)) {
                    $store['LIKE'][preg_replace('/\*/is', '%', $field)] = $value;
                    // $field = preg_replace('/^\*/is', '[LIKE]', $field);
                } else {
                    //$store['AND'][$field] = $value;
                    $store[$field] = $value;
                }
            }
        }

        return $this;
    }

    /**
     * Add new field to retrieve
     * 
     * @param string|array $field Field to retrieve for or array with multiple fields.
     * @return object|mixed
     */
    function field ($field=null)
    {
        if (!$field) {
            return (!is_array($this->fields) || count($this->fields) <= 1)? (string)$this->fields : $this->fields;
        }

        if (is_array($field)) {
            foreach ($field as $k => $v) {
                $this->field($v);
            }
        } else {
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
    function join ($table, $lKey, $rKey=null)
    {
        if (!is_array($table) && !$lKey) {
            return false;
        }

        $this->joins[$table] = ($rKey)? [$lKey => $rKey] : $lKey;

        return $this;
    }

    /**
     * Paginate results
     * 
     * @param integer $code Error code
     * @param string $message Error message
     * @return boolean
     */
    function paginate ()
    {
        Response::metadata('paging', $this->paging);
    }

    /**
     * Retrieve results quantity
     * 
     * Results must be populated from within the results method
     * @return integer
     */
    public function records () {
        return $this->paging['records'];
    }

    /**
     * Retrieve total pages quantity 
     * 
     * Results must be populated from within the results method
     * @return integer
     */
    public function pages () {

        // Division by zero not allowed
        if ($this->paging['limit'] == 0) {
            return 0;
        }

        return ceil($this->paging['records']/$this->paging['limit']);
    }

    /**
     * Retrieve primary key for this module
     * 
     * @return boolean
     */
    function primaryKey ()
    {
        return $this->primaryKey;
    }

    /**
     * Stores any given method for $ttl time
     * 
     * @param $ttl (Optional) Expiration time. If null, standard time is used.
     * @return object
     */
    public function remember ($ttl=null) {

        // ttl for this cache
        $ttl = ($ttl)?: $this->cache_ttl;

        return $this;
    }

}
