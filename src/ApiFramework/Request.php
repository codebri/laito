<?php

namespace ApiFramework;

class Request extends Core
{

    /**
     * @var array Reserved parameters
     */
    private $reserved = [
        'token',
        'locale',
        '_method'
    ];

    /**
     * @var array Received inputs
     */
    private $inputs = null;


    /**
     * Retrieves the request method
     *
     * @return string Method
     */
    public function method () {

        // Return the emulated method
        $emulated = filter_input(INPUT_GET, '_method', FILTER_SANITIZE_STRING);
        if ($this->app->config('request.emulate') && $emulated) {
            return $emulated;
        }

        // Or the real method
        return $_SERVER['REQUEST_METHOD'];
    }


    /**
     * Retrieves the current URL
     *
     * @return string URL
     */
    public function url () {
        $parts = explode('?', $_SERVER['REQUEST_URI']);
        return reset($parts);
    }


    /**
     * Retrieve token
     *
     * @return mixed Token or false
     */
    public function token () {
        $inputs = $this->getInputs();
        return isset($inputs['token']) ? $inputs['token'] : false;
    }


    /**
     * Retrieve locale
     *
     * @return mixed Locale or false
     */
    public function locale () {
        $inputs = $this->getInputs();
        return isset($inputs['locale']) ? $inputs['token'] : false;
    }


    /**
     * Return a request input
     *
     * @param string $input Input key
     * @param string $default Default value to return
     * @return mixed Array of inputs, or single input if a key is specified
     */
    public function input ($input = null, $default = null) {

        // Get all inputs
        $inputs = $this->getInputs();

        // Exclude reserved inputs
        foreach ($inputs as $key => $value) {
            if (in_array($key, $this->reserved)) {
                unset($inputs[$key]);
            }
        }

        // Return one input
        if ($input) {
            return isset($inputs[$input]) ? $inputs[$input] : $default;
        }

        // Or the complete array of inputs
        return $inputs;
    }


    /**
     * Return a request input
     *
     * @param string $input Input key
     * @return boolean Has the desired input or not
     */
    public function hasInput ($input) {
        return isset($this->inputs[$input]);
    }


    /**
     * Stores the parameters from the request in the inputs array
     *
     * @return array Array of inputs
     */
    private function getInputs () {

        // If defined, return the inputs array
        if ($this->inputs) {
            return $this->inputs;
        }

        // Check by request method
        switch ($_SERVER['REQUEST_METHOD']) {

            // Get PUT inputs
            case 'PUT':
                parse_str(file_get_contents("php://input"), $inputs);
                foreach ($inputs as $key => $value) {
                    $this->inputs[$key] = filter_var($value, FILTER_SANITIZE_STRING);
                }
                break;

            // Get POST inputs
            case 'POST':
                $this->inputs = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
                break;

            // Get GET inputs
            default:
                $this->inputs = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
                break;
        }

        // Return array of inputs
        return $this->inputs ? : [];
    }

}