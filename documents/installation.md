# Installation

Require Laito in your `composer.json` file:

```
{
    "require": {
      "mangolabs/apiframework": "dev-master"
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
    'database.name' => 'app',
    'database.username' => 'root',
    'database.password' => 'root',
]);
```

Or set them after initialization:

```
$app->config('database.name', 'test');
```

The complete list of options is below.

```
$defaultSettings = [
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
```