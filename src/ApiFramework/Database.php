<?php

namespace ApiFramework;

class Database extends \medoo
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
    public function __construct ($config, App $app) {
        parent::__construct($config);
        $this->app = $app;
    }

    protected function column_push($columns) {
        if ($columns == '*') {
            return $columns;
        }
        if (is_string($columns)) {
            $columns = array($columns);
        }
        $stack = array();
        foreach ($columns as $key => $value) {
            preg_match('/([a-zA-Z0-9_\-\.]*)\s*\(([a-zA-Z0-9_\-]*)\)/i', $value, $match);
            preg_match('/([A-Z0-9\_]+)\((.+?)\)\((.*?)\)/', $value, $match2);
            if (isset($match2[1], $match2[2])) {
                array_push($stack, $match2[1] .'('. $match2[2] . ') AS ' . $this->column_quote( $match2[3] ));
            } else if (isset($match[1], $match[2])) {
                array_push($stack, $this->column_quote( $match[1] ) . ' AS ' . $this->column_quote( $match[2] ));
            } else {
                array_push($stack, $this->column_quote( $value ));
            }
        }
        return implode($stack, ',');
    }

    public function last_query() {
        return str_replace('"', '', $this->queryString);
    }

}