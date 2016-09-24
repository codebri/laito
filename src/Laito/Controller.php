<?php namespace Laito;

use Laito\Core\Base;

class Controller extends Base {

    /**
     * @var Model repository
     */
    public $modelName;

    /**
     * Class constructor
     *
     * @param App $app App instance
     */
    public function __construct (App $app) {

        // Construct from parent
        parent::__construct($app);

        // Setup model
        if (isset($this->modelName)) {
            $this->model = $this->app->make($this->modelName);
        }

        // Initialize
        $this->initialize();
    }

    /**
     * Setup configurations and dependencies
     *
     * @return object Controller instance
     */
    public function initialize () {

        // Return instance
        return $this;
    }

    /**
     * Display a listing of the resource
     *
     * @param array $params Listing parameters
     * @return array Response
     */
    public function index ($params = []) {

        // Set the filters
        $params = (!empty($params))? $params : $this->app->request->input();

        // Get records
        $result = $this->model->search($params)->get();

        // Get pagination and number of records
        $pagination = array_merge(
            ['records' => $this->model->search($params)->count()],
            $this->model->pagination()
        );

        // Return results
        return [
            'success' => true,
            'paging' => $pagination,
            'data' => $result
        ];
    }

    /**
     * Display the specified resource
     *
     * @param array $id Resource ID
     * @return array Response
     */
    public function show ($id = null) {

        // Check the ID
        if (!isset($id)) {
            throw new \InvalidArgumentException('Undefined ID', 400);
        }

        // Get record
        $item = $this->model->find($id);

        // Abort if the record is not found
        if (!$item) {
            throw new \Exception('Element not found', 404);
        }

        // Return response
        return [
            'success' => true,
            'data' => $item
        ];
    }

    /**
     * Stores a newly created resource in storage
     *
     * @param array $params Resource attributes
     * @return array Response
     */
    public function store ($attributes = []) {

        // Set the attributes
        $attributes = (!empty($attributes))? $attributes : $this->app->request->input();

        // Create the record
        $result = $this->model->create($attributes);

        // Return results
        return [
            'success' => true,
            'id' => $result['id'],
            'data' => $result
        ];
    }

    /**
     * Update the specified resource in storage
     *
     * @param array $id Resource ID
     * @return array Response
     */
    public function update ($id = null, $attributes = []) {

        // Check the ID
        if (!isset($id)) {
            throw new \InvalidArgumentException('Undefined ID', 400);
        }

        // Set the attributes
        $attributes = (!empty($attributes))? $attributes : $this->app->request->input();

        // Update the record
        $result = $this->model->update($id, $attributes);

        // Return results
        return [
            'success' => true,
            'id' => $id,
            'data' => $result
        ];
    }

    /**
     * Remove the specified resource from storage
     *
     * @param array $id Resource ID
     * @return array Response
     */
    public function destroy ($id = null) {

        // Check the ID
        if (!isset($id)) {
            throw new \InvalidArgumentException('Undefined ID', 400);
        }

        // Delete the record
        $result = $this->model->destroy($id);

        // Return results
        return [
            'success' => true,
            'id' => $result
        ];
    }

}