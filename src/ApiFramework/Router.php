<?php namespace ApiFramework;

/**
 * Router class
 *
 * @package default
 * @author Mangolabs
 */

class Router extends Core
{

    /**
     * @var array HTTP methods
     */
    private $routes = [
        'get' => [],
        'post' => [],
        'put' => [],
        'delete' => [],
    ];


    /**
     * Register a url
     * 
     * @param string $method Method 
     * @param string $path Route to match
     * @param array $action Controller and method to execute
     * @param array $model Optional model to inject
     * @return boolean Success or fail of registration
     */
    public function register ($method, $path, $action, $model = null) {

        // Create arrays
        $params = [];
        $matches = [];
        $method = strtolower($method);

        // Replace placeholders
        if (preg_match_all('/\{([\w-]+)\}/i', $path, $matches)) {
            $params = end($matches);
        }
        $pattern = preg_replace(
            ['/(\{[\w-]+\})/i', '/\\//'],
            ['([\w-]+)', '\\/'],
            $path
        );

        // Store in the routes array
        return $this->routes[$method][$path] = [
            'path' => $path,
            'pattern' => '/^' . $pattern . '\\/?$/i',
            'class' => $action[0],
            'method' => $action[1],
            'params' => $params,
            'model' => $model
        ];
    }


    /**
     * Retrieve registered routes
     * 
     * @param string $method (Optional) Method specific routes.
     * @return array Array of routes
     */
    public function routes ($method = null) {
        if ($method && isset($this->methods[$method])) {
            return $this->routes[$method];
        }
        return $this->routes;
    }


    /**
     * Retrieve model and method to execute
     * 
     * @param string $route Route to match
     * @return array Action and parameters
     */
    public function getAction ($url) {

        // Get requested method
        $method = $this->app->request->method() ? : 'GET';
        $method = strtolower($method);

        // Check all routes until one matches
        foreach ($this->routes[$method] as $route) {
            $matches = [];
            if (preg_match($route['pattern'], $url, $matches)) {
                if (!empty($matches)) {
                    array_shift($matches);
                    $route['params'] = array_combine(
                        $route['params'],
                        $matches
                    );
                }
                return $route;
            }
        }

        // Return false if none of the routes matched
        return false;
    }


    /**
     * Register a resource
     * 
     * @param string $route Route for the resource
     * @param string $controller Controller name
     * @param string $model Optional model to inject
     * @return boolean Success or fail of registration
     */
    public function resource ($route, $controller, $model = null) {
        $this->register('get', $route, [$controller, 'index'], $model);
        $this->register('get', $route . '/{id}', [$controller, 'show'], $model);
        $this->register('post', $route, [$controller, 'store'], $model);
        $this->register('put', $route . '/{id}', [$controller, 'update'], $model);
        $this->register('delete', $route . '/{id}', [$controller, 'destroy'], $model);
        return true;
    }

    /**
     * Captures non existing functions
     * 
     * @param string $function Function requested to execute
     * @param array $params Params requested for function execution
     * @return boolean
     */
    public function __call ($function, $params)
    {
        if (isset($this->routes[$function])) {
            $this->register($function, $params[0], $params[1]);
            return true;
        }
        return false;
    }

}