<?php namespace Laito;

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
        'request.emulate'   => true,
        'database.type'     => false,
        'database.server'   => 'localhost',
        'database.name'     => 'test',
        'database.username' => 'root',
        'database.password' => 'root',
        'database.file'     => '',
        'public.url'        => 'localhost',
        'templates.path'    => 'templates'
    ];

    /**
     * Constructor
     *
     * @param array $userSettings Array of user defined options
     */
    public function __construct ($userSettings = []) {

        // Setup settings
        $this->container['settings'] = array_merge($this->defaultSettings, $userSettings);

        // Share an auth instance
        $this->container['auth'] = $this->share(function ($container) {
            return new Authentication($this);
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

        // If the app uses a database
        if ($this->config('database.type')) {

            // Share a PDO instance
            $this->container['pdo'] = $this->share(function ($container) {
                if ($this->config('database.type') === 'mysql') {
                    return new \PDO(
                        'mysql:dbname=' . $this->config('database.name') . ';host=' . $this->config('database.server'),
                        $this->config('database.username'),
                        $this->config('database.password')
                    );
                }
                if ($this->config('database.type') === 'sqlite') {
                    return new \PDO('sqlite:' . $this->config('database.file'), '', '', [\PDO::ATTR_PERSISTENT => true]);
                }
            });

            // Share a database instance
            $this->container['db'] = $this->share(function ($container) {
                return new Database ($this);
            });

            // Inject database connection to models
            $this->setupDatabaseConnection();
        }

        // Share a view instance
        $this->container['view'] = $this->share(function ($container) {
            return new View ($this);
        });

        // Share an HTTP instance
        $this->container['http'] = $this->share(function ($container) {
            return new Http ($this);
        });

        // Share a file instance
        $this->container['file'] = $this->share(function ($container) {
            return new File ($this);
        });

        // Share a mailing instance
        if (class_exists('\\PHPMailer')) {
            $this->container['mail'] = $this->share(function ($container) {
                return new \PHPMailer;
            });
        }
    }

    /**
     * Configure application settings
     *
     * @param string|array $name Setting to set or retrieve
     * @param mixed $value If passed, value to apply on the setting
     * @return mixed Value of a setting
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

        // Abort if the configuration key does not exist
        if (!isset($this->container['settings'][$name])) {
            throw new \Exception('Configuration key not defined: ' . $name, 500);
        }

        // Return value
        return $this->container['settings'][$name];
    }

    /**
     * Makes an instance of a class
     *
     * @param string $className Class name
     * @return object Class instance
     */
    public function make ($className) {

        // Create a reflection to access the class properties
        $reflection = new \ReflectionClass($className);

        // If the class has no constructor, just return a new instance
        $constructor = $reflection->getConstructor();
        if (is_null($constructor)) {
            return new $className;
        }

        // Or get the constructor parameters and instance dependencies
        $dependencies = [];
        $parameters = $reflection->getConstructor()->getParameters();
        foreach ($parameters as $param) {
            $class = $param->getClass();
            if ($class && $class->getName() === 'Laito\App') {

                // If the dependency is the app itself, inject the current instance
                $dependencies[] = $this;
            } else {

                // Otherwise, inject the instantiated dependency or a null value
                $dependencies[] = $class? $this->make($class->name) : 'NULL';
            }
        }

        // Return the class instance
        $instance = $reflection->newInstanceArgs($dependencies);

        // If the class has a boot method, run it
        if (method_exists($instance, 'boot')) {
            $instance->boot();
        }

        // Return instance
        return $instance;
    }

    /**
     * Runs the application
     *
     * @return string Response
     */
    public function run () {

        // Encapsulate to catch errors
        try {

            // Get URL
            $url = $this->request->url();

            // Get route action
            $action = $this->router->getAction($url);

            // Perform the filter
            $filter = $this->router->performFilter($action['filter']);

            // Check if the controller exists
            if (!isset($action) || !is_string($action['class']) || !class_exists($action['class'])) {
                throw new \Exception('Controller not found', 404);
            }

            // Create the required controller
            $controller = $this->make($action['class']);

            // Execute the required method and return the response
            return $this->response->output(call_user_func_array([$controller, $action['method']], $action['params']? : []));

        // Return validation errors
        } catch (Exceptions\ValidationException $e) {
            return $this->response->error($e->getCode(), $e->getMessage(), ['error' => ['errors' => $e->getErrors()]]);

        // Return database errors
        } catch (\PDOException $e) {

            // Show the invalid query
            echo '<pre>';
            print_r($this->db->statement->errorInfo());
            echo $this->db->lastQuery();
            exit;

        // Return generic error
        } catch (\Exception $e) {
            return $this->response->error($e->getCode(), $e->getMessage());
        }
    }

    /**
     * Setups a database connection for models
     *
     * @return void
     */
    private function setupDatabaseConnection () {
        Model::setupConnection($this->db);
    }

}