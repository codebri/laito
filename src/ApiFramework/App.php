<?php

namespace ApiFramework;

class App extends Container
{

    /**
     * @var array Default settings
     */
    private $defaultSettings = [
        'auth.table'        => 'users',
        'auth.username'     => 'email',
        'auth.password'     => 'password',
        'sessions.folder'   => 'storage/sessions/',
        'sessions.ttl'      => 3600,
        'sessions.cookie'   => 'token',
        'reminders.folder'  => 'storage/reminders/',
        'reminders.ttl'     => 3600,
        'reminders.suffix'  => 'reminders_',
        'lang.folder'       => 'static/languages/',
        'request.emulate'   => true,
        'database.type'     => 'mysql',
        'database.server'   => 'localhost',
        'database.name'     => 'test',
        'database.username' => 'root',
        'database.password' => 'root'
    ];

    /**
     * Constructor
     *
     * @param array $userSettings Array of user defined options
     */
    public function __construct(array $userSettings = array()) {

        // Setup settings
        $this->container['settings'] = array_merge($this->defaultSettings, $userSettings);

        // Share an auth instance
        $this->container['auth'] = $this->share(function ($container) {
            return new Auth($this);
        });

        // Share an user instance
        $this->container['user'] = $this->share(function ($container) {
            return new User($this);
        });

        // Share a lang instance
        $this->container['lang'] = $this->share(function ($container) {
            return new Lang($this);
        });

        // Share a request instance
        $this->container['request'] = $this->share(function ($container) {
            return new Request($this);
        });

        // Share a response instance
        $this->container['response'] = $this->share(function ($container) {
            return new Response($this);
        });

        // Share a router instance
        $this->container['router'] = $this->share(function ($container) {
            return new Router($this);
        });

        // Share a database instance
        $this->container['db'] = $this->share(function ($container) {
            $config = [
                'database_type' => $this->config('database.type'),
                'database_name' => $this->config('database.name'),
                'server'        => $this->config('database.server'),
                'username'      => $this->config('database.username'),
                'password'      => $this->config('database.password')
            ];
            return new Database($config, $this);
        });
    }

    /**
     * Configure application settings
     *
     * @param string|array $name Setting to set or retrieve
     * @param mixed $value If passed, value to apply on the setting
     * @return mixed Value of a setting if only one argument is a string
     */
    public function config ($name, $value = null) {

        // Check for massive assignaments
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->config($key, $value);
            }
            return true;
        }

        // Assign a new value
        if (isset($value)) {
            $this->container['settings'][$name] = $value;
        }

        // Or return the current value
        return isset($this->container['settings'][$name]) ? $this->container['settings'][$name] : null;
    }


    /**
     * Runs the application
     * 
     */
    public function run () {

        // Get route action
        list($action, $urlParams) = $this->router->getAction($url);

        // Check if the class exists
        if (!class_exists($action['class'])) {
            $this->response->error(404, 'Class not found');
        }

        // Create the required object
        $module = new $action['class']($this);

        // Apply limit
        $module->limit($this->request->input('limit'));

        // Apply offset
        $module->offset($this->request->input('offset'));

        // Apply order
        $module->order($this->request->input('order'));

        // Apply wheres
        $filters = $module->validFilters();
        foreach ($filters as $name => $field) {
            if ($this->request->hasInput($name)) {
                $module->where($field, $this->request->input($name));
            }
        }

        // Apply writable data
        $writableFields = $module->writableFields();
        foreach ($writableFields as $field) {
            if ($this->request->hasInput($field)) {
                $module->data($field, $this->request->input($field));
            }
        }

        // Execute the required method
        $res = call_user_func_array(array($module, $action['method']), $urlParams ? : []);

        // Return the response in the right format
        return $this->response->output($res);
    }

}