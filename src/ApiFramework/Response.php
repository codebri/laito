<?php

namespace ApiFramework;

/**
 * Response library
 *
 * Handles responses
 * @version 1.0
 * @package Response
*/
class Response extends Core
{

    private static $formats = ['json', 'html'];
    private static $format = 'json';
    private static $headers = [];
    private static $cookies = [];
    private static $error = false;
    private static $data;
    private static $metadata;
    private static $errors = [
        401 => 'HTTP/1.1 401 Unauthorized',
        403 => 'HTTP/1.1 403 Forbidden',
        404 => 'HTTP/1.1 404 Not Found',
        500 => 'HTTP/1.1 500 Internal Server Error',
    ];

    /**
     * Set an error in the response.
     *
     * @param int $code
     * @param string $message
     * @return boolean
     */
    public static function error ($code, $message)
    {
        return self::$error = ['code' => $code, 'message' => $message];
    }

    /**
     * Set the response data.
     *
     * @param array $data
     * @return boolean
     */
    public static function data ($data)
    {
        return self::$data = $data;
    }

    /**
     * Set the response metadata.
     *
     * @param array $data
     * @return boolean
     */
    public static function metadata ($key, $val)
    {
        return self::$metadata[$key] = $val;
    }

    /**
     * Set a header for the response.
     *
     * @param array $data
     * @return boolean
     */
    public static function header ($header)
    {
        return self::$headers[] = $header;
    }

    /**
     * Set a cookie for the response.
     *
     * @param array $data
     * @return boolean
     */
    public static function cookie ($key, $value)
    {
        return self::$cookies[] = [$key, $value];
    }

    /**
     * Set the response format.
     *
     * @param array $data
     * @return boolean
     */
    public static function format ($format)
    {
        if (!in_array($format, self::$formats)) {
            return false;
        }
        return self::$format = $format;
    }

    /**
     * Return the response.
     *
     * @param array $data
     * @return void
     */
    public static function output ()
    {
        // Create response array
        $response = ['success' => true];

        // Check errors
        $error = self::$error;
        if (!empty($error)) {
            self::header(self::$errors[$error['code']] ?: self::$errors[$error=500]);
            $response['success'] = false;
            $response['error'] = $error;
        }

        // Add metadata
        if (!$error && self::$metadata) {
            $response['metadata'] = self::$metadata;
        }

        // Add data
        if (!$error && self::$data) {
            $response['data'] = self::$data;
        }

        // Set format
        switch (self::$format) {
            case 'json':
                self::header('Content-type: application/json; charset=utf-8');
                $response = json_encode($response);
                break;
            case 'html':
                $response = self::html($response);
                break;
        }

        // Set cookies
        foreach (self::$cookies as $key => $value) {
            setcookie($key, $value, time() + 3600, '/');
        }

        // Set headers
        foreach (self::$headers as $header) {
            header($header);
        }

        // Return response
        echo $response;
    }

    /**
     * Format the response in HTML.
     *
     * @param array $data
     * @return void
     */
    private static function html ($data)
    {
        $return = '';
        foreach ($data as $key => $value) {
            $return .= '<li>' . $key . ': ' . (is_array($value) ? self::html($value) : $value) . '</li>';
        }
        return '<ul>' . $return . '</ul>';
    }

}