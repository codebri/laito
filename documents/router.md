# Router

With a route, you can bind an HTTP request of a certain type to a controller.

```
$app->route->register('GET', '/hello/world', ['HelloController', 'world'], 'MyFilter');
```

The parameters are:

- The HTTP method (GET, POST, PUT or DELETE)
- The route to match (For example, '/posts')
- An array containing the controller name and method to execute
- Optionally, a filter to execute before executing this action

---

## Simple route

For example, this route will match the GET requests to `/posts` and execute the method `index` in the `PostsController` class:

```
$app->router->register('GET', '/posts', ['PostsController', 'index']);
```

---

## Route placeholders

You can use wildcards to match variable pieces of the URL to your route. Define them in curly braces:

```
$app->router->register('GET', '/posts/{id}', ['PostsController', 'show']);
```

The matched values will be passed to the executed controller's method. So, if the following URL is requested:

```
GET /posts/14
```

The `show` method in `PostsController` can make use of the `id` value:

```
function show ($id) {
    echo $id; // 14
}
```

---

## Resources

A lot of times you'll find yourself declaring common routes for all your CRUD operations. In those cases, the resource method is a nice shortcut.

```
$app->router->resource('/users', 'UsersController', 'MyFilter');
```

The parameters are:

- The route to match (for example, '/posts')
- The controller name
- Optionally, the name of the filter to execute before this actions

It binds the following routes to the corresponding `UsersController` methods:

HTTP method  | Route | Controller | Method
-------------| ------|------------|-------
GET | /users | UsersController | index
GET | /users/{id} | UsersController | show
POST | /users | UsersController | store
PUT | /users/{id} | UsersController | update
DELETE | /users/{id} | UsersController | destroy

---

## Filters

You can bind filters to be performed before the actions you declare in your routes.

```
$app->router->filter('auth', ['UsersController', 'auth']);
```

The parameters are:

- The filter name (for example, 'auth'), that will be used to reference it in the route declarations
- An array containing the class (usually, a controller) and method to execute

Instead of a class and method, you can use an anonymous function (closure):

```
$app->router->filter('disabled', function () use ($app) {
    $auth = $app->auth->check($app->request->token());
    if ($auth['user']['disabled']) {
        throw new Exception('Your user is disabled', 401);
    }
});
```

You can perform any actions any conditions in your filters, like checking the user's session and permissions or the request format. If the conditions are not met, you can stop the execution of the route's action by throwing an exception like is shown in the example above.

---

## Run

After you declared all your application routes, you can start listening for requests:

```
$app->run();
```
