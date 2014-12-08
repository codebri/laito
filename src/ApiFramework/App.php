<?php namespace ApiFramework;

/**
 * App class
 *
 * @package default
 * @author Mangolabs
 */

class App extends Container
{

    /**
     * @var array Default settings
     */
    private $defaultSettings = [
        'debug.queries'     => false,
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
        'database.password' => 'root',
        'public.url'        => 'localhost'
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

        // Share a PDO instance
        $this->container['pdo'] = $this->share(function ($container) {
            return new \PDO(
                'mysql:dbname=' . $this->config('database.name') . ';host=' . $this->config('database.server'),
                $this->config('database.username'),
                $this->config('database.password')
            );
        });

        // Share a database instance
        $this->container['db'] = $this->share(function ($container) {
            return new Database ($this);
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

        // Get URL
        $url = $this->request->url();

        // Get route action
        $action = $this->router->getAction($url);

        // Check if the controller exists
        if (!isset($action) || !class_exists($action['class'])) {
            $this->response->error(404, 'Controller not found');
        }

        // Instance model
        $model = null;
        if (isset($action['model'])) {
            if (!class_exists($action['model'])) {
                $this->response->error(404, 'Model not found');
            } else {
                $model = new $action['model']($this);
            }
        }

        // Create the required controller
        $controller = new $action['class']($this, $model);

        // Execute the required method
        $res = call_user_func_array(array($controller, $action['method']), $action['params'] ? : []);

        // Return the response in the right format
        return $this->response->output($res);
    }

}