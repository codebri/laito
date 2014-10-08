<?php

namespace ApiFramework;

/**
 * Request library
 *
 * Handles requests
 * @version 1.0
 * @package Request
*/
class Request extends Core
{

    private static $reserved = [
        'id',
        'module',
        'token',
        'limit',
        'offset',
        'order',
        'locale',
        '_method'
    ];

    private static $default = [
        'limit' => 10,
        'offset' => 0
    ];

    private static $inputs = false;

    /**
     * Retrieve the request method.
     *
     * @param string $key
     * @return string
     */
    public static function method ()
    {
        $override = filter_input(INPUT_GET, '_method', FILTER_SANITIZE_STRING);
        return $override?: $_SERVER['REQUEST_METHOD'];
    }


    /**
     * Retrieve requested url
     *
     * @param string $key
     * @return mixed
     */
    public static function url ()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $parts = explode('?', $uri);
        return reset($parts);
    }


    /**
     * Retrieve limit
     *
     * @return mixed
     */
    public static function limit ()
    {
        return filter_input(INPUT_GET, 'limit', FILTER_SANITIZE_STRING)?: self::$default['limit'];
    }


    /**
     * Retrieve offset
     *
     * @return mixed
     */
    public static function offset ()
    {
        return filter_input(INPUT_GET, 'offset', FILTER_SANITIZE_STRING)?: self::$default['offset'];
    }


    /**
     * Retrieve order
     *
     * @return mixed
     */
    public static function order ()
    {
        return filter_input(INPUT_GET, 'order', FILTER_SANITIZE_STRING);
    }


    /**
     * Retrieve token
     *
     * @return mixed
     */
    public static function token ()
    {
        $inputs = self::inputs();
        return isset($inputs['token'])? $inputs['token'] : false;
    }


    /**
     * Retrieve locale
     *
     * @return mixed
     */
    public static function locale ()
    {
        $inputs = self::inputs();
        return $inputs['locale']?: false;
    }


    /**
     * Return a request variable
     *
     * @param string $key
     * @return mixed
     */
    public static function input ($key = false)
    {
        $inputs = self::inputs();

        $allowed = [];
        foreach ($inputs as $input => $value) {
            if (!in_array($input, self::$reserved)) {
                $allowed[$input] = $value;
            }
        }

        return ($key)? $allowed[$key] : $allowed;
    }


    /**
     * Return an array of values from the query string.
     *
     * @param string $key
     * @return mixed
     */
    private static function inputs ()
    {
        if (is_array(self::$inputs)) {
            return self::$inputs;
        }

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'PUT':
                parse_str(file_get_contents("php://input"), $inputs);
                foreach ($inputs as $key => $value) {
                    self::$inputs[$key] = filter_var($value, FILTER_SANITIZE_STRING);
                }
                break;
            case 'POST':
                self::$inputs = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
                break;
            default:
                self::$inputs = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
                break;
        }

        return self::$inputs;
    }

}
