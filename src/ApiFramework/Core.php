<?php namespace ApiFramework;

/**
 * Core class
 *
 * @package default
 * @author Mangolabs
 */

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

}