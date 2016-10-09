<?php
namespace Laito;

use Laito\Core\Container;

class App extends Container
{
    /**
     * @var array Default settings
     */
    private $defaultSettings = [

        // If set to true, shows the SQL errors information in the response
        'debug.queries' => true,

        // If set to true, shows the backtrace on PHP errors in the response
        'debug.backtrace' => true,

        // Path of the queries log, or false if disabled
        'queries.log' => false,

        // Accepts '_method' parameter to emulate requests
        'request.emulate' => true,

        // Database type ('mysql', 'slqite' or false)
        'database.type' => false,

        // MySQL database server
        'database.server' => 'localhost',

        // MySQL database name
        'database.name' => 'test',

        // MySQL database username
        'database.username' => 'root',

        // MySQL database password
        'database.password' => 'root',

        // SQLite database file path
        'database.file' => '',

        // Path of the templates folder
        'templates.path' => 'templates',

        // Path of the tokens folder
        'tokens.storage' => 'storage/tokens',

        // Base URL root for the API
        'url.root' => ''
    ];

    /**
     * @var array Service providers
     */
    private $serviceProviders;

    /**
     * @var array Deafault service providers
     */
    private $deafultServiceProviders = [
        'http' => 'Laito\Http\Client',
        'tokens' => 'Laito\Session\Tokens\FileTokens',
        'session' => 'Laito\Session\Session',
        'request' => 'Laito\Http\Request',
        'response' => 'Laito\Http\Response',
        'router' => 'Laito\Router',
        'view' => 'Laito\View'
    ];

    /**
     * Constructor
     *
     * @param array $settings Array of user defined options
     * @param array $providers Array of user providers
     */
    public function __construct($settings = [], $providers = [])
    {
        // Setup settings
        $this->container['settings'] = array_merge($this->defaultSettings, $settings);

        // Setup service providers
        $this->serviceProviders = array_merge($this->deafultServiceProviders, $providers);

        // Register service providers
        $this->registerServiceProviders();

        // Register database connection
        $this->registerDatabaseConnection();
    }

    /**
     * Configure application settings
     *
     * @param string|array $name Setting to set or retrieve
     * @param mixed $value If passed, value to apply on the setting
     * @return mixed Value of a setting
     */
    public function config($name, $value = null)
    {
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
    public function make($className)
    {
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
    public function run()
    {
        try {
            $this->runAction();
        } catch (Exceptions\ValidationException $e) {
            return $this->returnValidationErrorResponse($e);
        } catch (\PDOException $e) {
            return $this->returnDatabaseErrorResponse($e);
        } catch (\Exception $e) {
            return $this->returnGeneralErrorResponse($e);
        }
    }

    /**
     * Registers the service providers
     *
     * @return void
     */
    private function registerServiceProviders()
    {
        foreach ($this->serviceProviders as $key => $serviceClass) {
            $this->container[$key] = $this->share(function () use ($serviceClass) {
                return $this->make($serviceClass);
            });
        }
    }

    /**
     * Registers the service providers
     *
     * @return void
     */
    private function registerDatabaseConnection()
    {
        // If the app uses a database
        if ($this->config('database.type')) {

            // Share a PDO instance
            $this->container['pdo'] = $this->share(function () {

                // Setup MySQL
                if ($this->config('database.type') === 'mysql') {
                    return new \PDO(
                        'mysql:dbname=' . $this->config('database.name') . ';host=' . $this->config('database.server'),
                        $this->config('database.username'),
                        $this->config('database.password')
                    );
                }

                // Setup SQLite
                if ($this->config('database.type') === 'sqlite') {
                    return new \PDO('sqlite:' . $this->config('database.file'), '', '', [\PDO::ATTR_PERSISTENT => true]);
                }
            });

            // Share a database instance
            $this->container['db'] = $this->share(function () {
                return $this->make('Laito\Database');
            });

            // Inject database connection to models
            Model::setupConnection($this->db);
        }
    }

    /**
     * Setups a database connection for models
     *
     * @return void
     */
    private function runAction()
    {
        // Get URL
        $url = $this->request->url();

        // Get route action
        $action = $this->router->getAction($url);

        // Perform the filter
        if ($action['filter']) {
            $filter = $this->router->performFilter($action['filter']);
        }

        // Check if the controller exists
        if (!isset($action) || !is_string($action['class']) || !class_exists($action['class'])) {
            throw new \Exception('Controller not found', 404);
        }

        // Create the required controller
        $controller = $this->make($action['class']);

        // Execute the required method and return the response
        return $this->response->output(call_user_func_array([$controller, $action['method']], $action['params']? : []));
    }

    /**
     * Returns a validation error response
     *
     * @return array Response
     */
    private function returnValidationErrorResponse($e)
    {
        return $this->response->error($e->getCode(), $e->getMessage(), ['error' => ['errors' => $e->getErrors()]]);
    }

    /**
     * Returns a database error response
     *
     * @return array Response
     */
    private function returnDatabaseErrorResponse($e)
    {
        $info = $this->config('debug.queries')? ['error' => ['errors' => $this->db->statement->errorInfo(), 'last_query' => $this->db->lastQuery()]] : [];
        return $this->response->error(500, 'Database error', $info);
    }

    /**
     * Returns a generic error response
     *
     * @return array Response
     */
    private function returnGeneralErrorResponse($e)
    {
        $backtrace = $this->config('debug.backtrace')? ['error' => ['backtrace' => $e->getTrace()]] : [];
        return $this->response->error($e->getCode(), $e->getMessage(), $backtrace);
    }

}
