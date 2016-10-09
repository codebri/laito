<?php
namespace Laito\Core;

use Laito\App;

class Base
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
    public function __construct(App $app)
    {
        $this->app = $app;
    }

}