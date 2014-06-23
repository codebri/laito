<?php

namespace ApiFramework;

/**
 * Router
 *
 * Base model 
 * @author Alejandro Garcia del Rio <alejandro.garciadelrio@loogares.com>
 * @version 1.0
 * @package Router
*/
class Router extends Core
{

    private static $methods = [
        'get' => [],
        'post' => [],
        'put' => [],
        'delete' => [],
    ];

    private static $resource = [
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
    static public function register ($method, $route, $action)
    {
        return self::$methods[$method][$route] = ['class' => $action[0], 'method' => $action[1]];
    }

    /**
     * Retrieve registered routes
     * 
     * @param string $method (Optional) Method specific routes.
     * @return array
     */
    static public function routes ($method=null)
    {
        if ($method && isset(self::$methods[$method])) {
            return self::$methods[$method];
        }
        return self::$methods;
    }

    /**
     * Retrieve model and method to execute
     * 
     * @param string $route Route to match
     * @return array
     */
    static public function getAction ($route)
    {
        // Get requested method
        $method = Request::method()?: 'GET';
        $method = strtolower($method);

        // Check route
        if (!isset($methods[$method][$route])) {
            return self::error(404, 'Not found');
        }

        // Try literal match
        if ($match = self::$methods[$method][$route]) {
            return [$match, []];
        }

        // Generate regex pattern from registered route and test it against requested route
        foreach (self::$methods[$method] as $key => $val) {
            $pattern = preg_replace('/\/\:.+?\//is', '/(.+?)/', $key);
            $pattern = '/^'.str_replace('/', '\/', $pattern).'$/is';
            if (preg_match($pattern, $route, $matches)) {
                return [$val, array_slice($matches, 1)];
            }
        }

        // Or return an error page
        return self::error(404, 'Not found');
    }


    /**
     * Register a resource
     * 
     * @param string $route Route for the resource
     * @param string $module Module name
     * @return boolean
     */
    static public function resource ($route, $module)
    {
        // Resource map template
        $resource = [];

        // Replace placeholders
        foreach (self::$resource as $verb => $routes) {
            foreach ($routes as $k => $v) {
                $v['class'] = $module;
                $new_route = preg_replace('/%route%/', $route, $k);
                self::$methods[$verb][preg_replace('/\/\//is', '/', $new_route)] = $v;
            }
        }

        return true;
    }


    /**
     * Captures non existing static functions
     * 
     * @param string $function Function requested to execute
     * @param array $params Params requested for function execution
     * @return boolean
     */
    public static function __callStatic ($function, $params)
    {
        if (isset(self::$methods[$function])) {
            self::register($function, $params[0], $params[1]);
            return true;
        }
        return false;
    }

}
