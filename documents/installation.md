# Installation

Require Laito in your `composer.json` file:

```
{
    "require": {
      "codebri/laito": "dev-master"
    }
}
```

And run `composer install`. This will fetch and save the package and its dependencies into the `vendor` folder.

Once you installed it, create an `index.php` file in the route of your project and include the Composer's autoload file:

```
require_once __DIR__ . '/vendor/autoload.php';
```

All requests have to be pointed to that `index.php` file. In Apache, you can do so by creating an `.htaccess` file with this rules:

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?route=$1 [L,QSA]
```

---

## Usage and configuration

Now you're ready to start using the framework. Create an app instance in your `index.php` file:

```
$app = new Laito\App();
```

You can pass an array of configuration options:

```
$app = new Laito\App([
    'database.type' => 'mysql',
    'database.server' => 'localhost',
    'database.name' => 'app',
    'database.username' => 'root',
    'database.password' => 'root',
    'queries.log' => 'storage/queries.log'
]);
```

Or set them after initialization:

```
$app->config('database.name', 'test');
```

The complete list of options is below.

```
$defaultSettings = [
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
    'tokens.storage' => 'storage/tokens'
];
```