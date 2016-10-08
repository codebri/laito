<?php
namespace Laito;

use Laito\Core\Base;

class Router extends Base
{

    /**
     * @var array Routes holder
     */
    private $routes = [
        'get' => [],
        'post' => [],
        'put' => [],
        'delete' => [],
    ];

    /**
     * @var array Filters holder
     */
    private $filters = [];

    /**
     * @var array Applied filters
     */
    private $appliedFilters = [];

    /**
     * Register a url
     *
     * @param string $method Method
     * @param string $path Route to match
     * @param array $action Controller and method to execute
     * @param array $filter Optional filter to execute
     * @return boolean Success or fail of registration
     */
    public function register($method, $path, $action, $filter = null)
    {
        // Add leading slash if not present
        if (strncmp($path, '/', 1) !== 0) {
            $path = '/' . $path;
        }

        // Add API base url to the path
        $path = $this->app->config('url.root') . $path;

        // Create arrays
        $params = [];
        $matches = [];
        $method = strtolower($method);

        // Replace placeholders
        if (preg_match_all('/\{([\w-\.]+)\}/i', $path, $matches)) {
            $params = end($matches);
        }
        $pattern = preg_replace(
            ['/(\{[\w-\.]+\})/i', '/\\//'],
            ['([\w-\.]+)', '\\/'],
            $path
        );

        // Store in the routes array
        return $this->routes[$method][$path] = [
            'path' => $path,
            'pattern' => '/^' . $pattern . '\\/?$/i',
            'class' => $action[0],
            'method' => $action[1],
            'params' => $params,
            'filter' => $filter
        ];
    }

    /**
     * Retrieve registered routes
     *
     * @param string $method (Optional) Method specific routes.
     * @return array Array of routes
     */
    public function routes($method = null)
    {
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
    public function getAction($url)
    {
        // Current route holder
        $current =  false;

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
                $current = $route;
                break;
            }
        }

        // Abort if none of the routes matched
        if (!$current) {
            throw new \Exception('Route not found', 404);
        }

        // Return current route
        return $current;
    }

    /**
     * Register a resource
     *
     * @param string $route Route for the resource
     * @param string $controller Controller name
     * @param string $filter Optional filter to execute
     * @return boolean Success or fail of registration
     */
    public function resource($route, $controller, $filter = null)
    {
        $this->register('get', $route, [$controller, 'index'], $filter);
        $this->register('get', $route . '/{id}', [$controller, 'show'], $filter);
        $this->register('post', $route, [$controller, 'store'], $filter);
        $this->register('put', $route . '/{id}', [$controller, 'update'], $filter);
        $this->register('delete', $route . '/{id}', [$controller, 'destroy'], $filter);
        return true;
    }

    /**
     * Sets a route filter
     *
     * @param string $filterName Filter name
     * @param array $callback Callback to exectue
     * @return boolean Success or failure of registration
     */
    public function filter($filterName, $callback)
    {
        return $this->filters[$filterName] = $callback;
    }

    /**
     * Returns a route filter
     *
     * @param string $filterName Filter name
     * @return mixed Registered filter
     */
    public function getFilter($filterName = null)
    {
        return isset($this->filters[$filterName])? $this->filters[$filterName] : false;
    }

    /**
     * Sets an applied filter
     *
     * @param string $filterName Filter name
     * @return mixed Registered filter
     */
    public function setAppliedFilter($filterName = null)
    {
        return $this->appliedFilters[] = $filterName;
    }

    /**
     * Sets an applied filter
     *
     * @param string $filterName Filter name
     * @return mixed Registered filter
     */
    public function filtered($filterName = null)
    {
        return in_array($filterName, $this->appliedFilters);
    }

    /**
     * Performs a filter
     *
     * @param string $filter Filter name
     */
    public function performFilter($filter)
    {
        $filter = $this->getFilter($filter);

        if (!$filter) {
            throw new \Exception('Filter not found', 500);
        }

        if ($filter instanceof \Closure) {
            call_user_func($filter);
            return $this->setAppliedFilter($filter);
        }

        if (is_array($filter)) {
            list($class, $method) = $filter;
            if (!class_exists($class)) {
                throw new \Exception('Invalid filter class', 500);
            }

            $class = $this->app->make($class);
            call_user_func([$class, $method]);
            return $this->setAppliedFilter($filter);
        }

        throw new \Exception('Invalid filter', 500);
    }

    /**
     * Register a group of routers with a common filter
     *
     * @param string $filter Filter name
     * @param string $prefix Prefix for all routes
     * @param array $routers Routes
     */
    public function group()
    {
        // Get arguments
        if (func_num_args() === 2) {
            list($prefix, $routes) = func_get_args();
            $filter = null;
        } elseif (func_num_args() === 3) {
            list($filter, $prefix, $routes) = func_get_args();
        }

        // Add leading slash to prefix if not present
        if (strncmp($prefix, '/', 1) !== 0) {
            $prefix = '/' . $prefix;
        }

        // Add trailing slash to prefix if not present
        if (substr($prefix, -1) !== '/') {
            $prefix .= '/';
        }

        // Register routes
        foreach ($routes as $route) {
            $type = $route[0];
            if ($type === 'resource') {
                $this->resource($prefix . $route[1], $route[2], $filter);
            } else {
                $this->register($type, $prefix . $route[1], $route[2], $filter);
            }
        }
    }

    /**
     * Captures non existing functions
     *
     * @param string $function Function requested to execute
     * @param array $params Params requested
     * @return boolean
     */
    public function __call($function, $param)
    {
        if (isset($this->routes[$function])) {
            if (isset($params[2])) {
                $this->register($function, $params[0], $params[1], $params[2]);
            } else {
                $this->register($function, $params[0], $params[1]);
            }
            return true;
        }
        return false;
    }

}