<?php

namespace ApiFramework;

class Core
{

    /**
     * @var App $app Application instance
     */
    public $app;


    /**
     * Constructor
     *
     * @param App $app Application instance
     */
    public function __construct(App $app) {
        $this->app = $app;
    }


    /**
     * Returns an error and stops the application
     *
     * @return string Error message
     */
    public function error ($code, $message) {
        die('Error ' . $code  . ': ' . $message);
    }

}