<?php

namespace ApiFramework;

class Router extends Core
{

    /**
     * @var array HTTP methods
     */
    private $methods = [
        'get' => [],
        'post' => [],
        'put' => [],
        'delete' => [],
    ];

    /**
     * @var array Resource template
     */
    private $resource = [
        'get' => [
            '/%route%/' => ['class' => '', 'method' => 'index'],
            '/%route%/:id/' => ['class' => '', 'method' => 'show'],
        ],
        'post' => [
            '/%route%/' => ['class' => '', 'method' => 'create'],
        ],
        'put' => [
            '/%route%/:id/' => ['class' => '', 'method' => 'update'],
        ],
        'delete' => [
            '/%route%/:id/' => ['class' => '', 'method' => 'destroy'],
        ],
    ];


    /**
     * Register a url
     * 
     * @param string $method Method 
     * @param string $route Route to match
     * @param array $action Module and method to execute
     * @return boolean
     */
    public function register ($method, $route, $action) {
        return $this->methods[$method][$route] = ['class' => $action[0], 'method' => $action[1]];
    }


    /**
     * Retrieve registered routes
     * 
     * @param string $method (Optional) Method specific routes.
     * @return array
     */
    public function routes ($method = null) {
        if ($method && isset($this->methods[$method])) {
            return $this->methods[$method];
        }
        return $this->methods;
    }


    /**
     * Retrieve model and method to execute
     * 
     * @param string $route Route to match
     * @return array
     */
    public function getAction ($route) {

        // Get requested method
        $method = $this->app->request->method() ? : 'GET';
        $method = strtolower($method);

        // Try literal match
        if (isset($methods[$method][$route]) && $match = $this->methods[$method][$route]) {
            return [$match, []];
        }

        // Generate regex pattern from registered route and test it against requested route
        foreach ($this->methods[$method] as $key => $val) {
            $pattern = preg_replace('/\/\:.+?\//is', '/(.+?)/', $key);
            $pattern = '/^'.str_replace('/', '\/', $pattern).'$/is';
            if (preg_match($pattern, $route, $matches)) {
                return [$val, array_slice($matches, 1)];
            }
        }

        // Or return an error page
        return $this->error(404, 'Not found');
    }


    /**
     * Register a resource
     * 
     * @param string $route Route for the resource
     * @param string $module Module name
     * @return boolean
     */
    public function resource ($route, $module) {

        // Resource map template
        $resource = [];

        // Replace placeholders
        foreach ($this->resource as $verb => $routes) {
            foreach ($routes as $k => $v) {
                $v['class'] = $module;
                $new_route = preg_replace('/%route%/', $route, $k);
                $this->methods[$verb][preg_replace('/\/\//is', '/', $new_route)] = $v;
            }
        }
        return true;
    }

}