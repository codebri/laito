<?php namespace ApiFramework;

/**
 * Controller class
 *
 * @package default
 * @author Mangolabs
 */

class Controller extends Core {

    /**
     * @var Model repository
     */
    public $model;

    /**
     * Class constructor
     *
     * @param App $app App instance
     * @param object $model Model instance
     */
    public function __construct (App $app, $model = null) {

        // Construct from parent
        parent::__construct($app);

        // Reference model
        if ($model) {
            $this->model = $model;
        }
    }

    /**
     * Display a listing of the resource
     *
     * @param array $params Listing parameters
     * @return string Response
     */
    public function index ($filters = []) {

        // Set the filters
        $filters = (!empty($filters))? $filters : $this->app->request->input();

        // Get records
        $result = $this->model->search($filters)->get();

        // Return on fails
        if (!$result) {
            return $this->failed();
        }

        // Get pagination and number of records
        $pagination = array_merge(
            ['records' => $this->model->count()],
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
     * @return string Response
     */
    public function show ($id = null) {

        // Get records
        $result = $this->model->find($id);

        // Return on fails
        if (!$result) {
            return $this->failed($id);
        }

        // Return results
        return [
            'success' => true,
            'data' => $result
        ];
    }

    /**
     * Stores a newly created resource in storage
     *
     * @param array $params Resource attributes
     * @return string Response
     */
    public function store ($attributes = []) {

        // Set the attributes
        $attributes = (!empty($attributes))? $attributes : $this->app->request->input();

        // Create the record
        $result = $this->model->create($attributes);

        // Return on fails
        if (!$result) {
            return $this->failed(null, $attributes);
        }

        // Return results
        return [
            'success' => true,
            'id' =>$result['id'],
            'data' => $result
        ];
    }

    /**
     * Update the specified resource in storage
     *
     * @param array $id Resource ID
     * @return string Response
     */
    public function update ($id = null, $attributes = []) {

        // Set the attributes
        $attributes = (!empty($attributes))? $attributes : $this->app->request->input();

        // Update the record
        $result = $this->model->update($id, $attributes);

        // Return errors
        if (!$result) {
            return $this->failed($id, $attributes);
        }

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
     * @return string Response
     */
    public function destroy ($id = null) {

        // Delete the record
        $result = $this->model->destroy($id);

        // Return on fails
        if (!$result) {
            return $this->failed($id);
        }

        // Return results
        return [
            'success' => true,
            'id' => $result
        ];
    }

    /**
     * Returns a failed response
     *
     * @param array $id Resource ID
     * @param array $attributes Invalid attributes
     * @return string Response
     */
    private function failed ($id = null, $attributes = null) {

        // Create response
        $response = ['success' => false];

        // Set ID
        if ($id) {
            $response['id'] = $id;
        }

        // Set error list
        if ($attributes && is_array($attributes)) {
            $errors = $this->model->validationErrors($attributes);
            if ($errors) {
                $response['errors'] = $errors;
            }
        }

        // Return response
        return $response;
    }

}