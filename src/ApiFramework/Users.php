<?php

namespace ApiFramework;

class Users extends BaseModule
{

    /**
     * Class constructor
     *
     * @param App Application instance
     */
    function __construct (App $app) {
        parent::__construct($app);

        // Set table
        $table = $this->app->config('auth.table');
        $this->table = $table;

        // Set username field
        $username = $this->app->config('auth.username');
        $this->username = $username;

        // Set password field
        $password = $this->app->config('auth.password');
        $this->password = $password;

        // Set writables
        $this->writables = [$username, $password];

        // Set valid filters
        $this->validFilters[
            'id' => $table . '.id',
            $username => $table . '.' . $username
        ];
    }


    /**
     * Login a user
     *
     * @param string $username Username to login
     * @param string $password Password
     */
    public function login ($username = null, $password = null) {

        // Get credentials
        $username = isset($username) ? $username : $this->data[$this->username];
        $password = isset($password) ? $password : $this->data[$this->password];

        // Attempt to login
        $token = $this->app->auth->attempt($username, $password);

        // Check errors
        if (!$token) {
            $this->app->response->error(401, 'Incorrect username or password');
        }

        // Return token
        return ['success' => true, 'data' => ['user' => $username, 'token' => $token]]
    }

}