<?php namespace ApiFramework;

/**
 * Response class
 *
 * @package default
 * @author Mangolabs
 */

class Response extends Core
{

    /**
     * @var string Allowed response formats
     */
    private $allowedFormats = ['json', 'html'];

    /**
     * @var string Response format
     */
    private $format = 'json';

    /**
     * @var array Response headers
     */
    private $headers = [];

    /**
     * @var array Response cookies
     */
    private $cookies = [];

    /**
     * @var array Error codes and messages
     */
    private $errors = [
        401 => 'HTTP/1.1 401 Unauthorized',
        403 => 'HTTP/1.1 403 Forbidden',
        404 => 'HTTP/1.1 404 Not Found',
        500 => 'HTTP/1.1 500 Internal Server Error',
    ];


    /**
     * Sets a header for the response
     *
     * @param string $header Header to set
     * @return object Response instance
     */
    public function header ($header) {
        $this->headers[] = $header;
        return $this;
    }


    /**
     * Sets a cookie for the response
     *
     * @param string $key Cookie key
     * @param string $value Cookie value
     * @return object Response instance
     */
    public function cookie ($key, $value) {
        $this->cookies[$key] = $value;
        return $this;
    }


    /**
     * Sets the format of the response
     *
     * @param string $format
     * @return object Response instance
     */
    public function format ($format) {
        if (in_array($format, $this->allowedFormats)) {
            $this->format = $format;
        }
        return $this;
    }


    /**
     * Echoes out the response
     *
     * @param array $response Response data
     */
    public function output ($response = []) {

        // Set format
        switch ($this->format) {
            case 'json':
                $this->header('Content-type: application/json; charset=utf-8');
                $response = json_encode($response);

                // Replace string numbers for integers
                $response = preg_replace('/(")([0-9]+)(")/is', '\\2', $response);
                break;
            case 'html':
                $this->header('Content-type: text/html; charset=utf-8');
                $response = $this->html($response);
                break;
        }

        // Set cookies
        foreach ($this->cookies as $key => $value) {
            setcookie($key, $value, time() + 3600, '/');
        }

        // Set headers
        foreach ($this->headers as $header) {
            header($header);
        }

        // Return response
        echo $response;
        exit;
    }


    /**
     * Sets an error header and echoes out the response
     *
     * @param array $response Response data
     */
    public function error ($code, $message) {
        $response['success'] = false;
        $response['error']['code'] = $code;
        if (in_array($code, array_keys($this->errors))) {
            $this->header($this->errors[$code]);
            $response['error']['status'] = $this->errors[$code];
        }
        $response['error']['message'] = $message;
        return $this->output($response);
    }


    /**
     * Transforms an array into an HTML list
     *
     * @param array $data Array of data to transform
     * @return string HTML list
     */
    private function html ($data) {
        $return = '';
        foreach ($data as $key => $value) {
            $return .= '<li>' . $key . ': ' . (is_array($value) ? $this->html($value) : $value) . '</li>';
        }
        return '<ul>' . $return . '</ul>';
    }


    /**
     * Groups elements with similar keys into an object
     * 
     * @param string $collection The collection to convert
     * @param string $keys Name of keys to objectify
     * @return array The resulting array
     */
    public function objectify ($collection, $indexes) {
        foreach ($collection as $k => $v) {
            if (is_array($v)) {
                $collection[$k] = self::objectify($v, $indexes);
            } else if ( ($key = current(explode('_', $k))) && in_array($key, $indexes) ) {
                $collection[$key][str_replace($key.'_', '', $k)] = $v;
                unset($collection[$k]);
            }
        }
        return $collection;
    }

    /**
     * Transforms duplicate rows into one row with its 
     * inner collections for one to many relations
     * 
     * @param string $collection The collection to convert
     * @return array The resulting array
     */
    public function joinToCollection ($collection) {

        $data = [];
        $duplicates = false;
        $changedKeys = [];
        if (is_array($collection)) {
            foreach ($collection as $k => $v) {
                if (isset($data[$v['id']])) {
                    // There is at least 1 duplicate
                    $duplicates = true;
                    // Iterates keys and merge arrays
                    foreach ($v as $key => $val) {
                        if (is_array($val) && (serialize($data[$v['id']][$key]) != serialize($val)) ) {
                            $changedKeys[$key] = 1;
                            // Force numeric key array
                            if (!isset($data[$v['id']][$key][0])) {
                                $_tmp = $data[$v['id']][$key];
                                unset($data[$v['id']][$key]);
                                $data[$v['id']][$key][0] = $_tmp;
                            }
                            array_push($data[$v['id']][$key], $val);
                        }
                    }
                }
                else {
                    $data[$v['id']] = $v;
                }
            }

        }

        if ($duplicates) {

            // Force numeric arrays in every changed array for data normalization
            foreach ($data as $rowId => $row) {
                foreach ($row as $fieldKey => $field) {
                    if ($changedKeys[$fieldKey]) {
                        // Force numeric key array
                        if (!isset($field[0])) {

                            // Remove empty arrays
                            $empty = true;
                            foreach ($field as $key => $value) {
                                if (!empty($value)) {
                                    $empty = false;
                                }
                            }
                            if ($empty) {
                                $data[$rowId][$fieldKey] = [];
                            }
                            // Change associative array for numeric array
                            else {
                                $_tmp = $field;
                                unset($data[$rowId][$fieldKey]);
                                $data[$rowId][$fieldKey][0] = $_tmp;
                            }
                        }
                    }
                }
            }

            // Store new data
            $collection = array_values($data);
        }

        return $collection;
    }
}