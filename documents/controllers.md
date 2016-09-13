# Controllers

The controllers are called by the application when a route asociated with them is called. The controllers are normal classes, and can have as many functions you want.

```php
class SomeController
{

    function hello () {
        echo 'Hello!';
    }

}
```

---

## Model injection

Many times, your controllers will make use of a model. But creating an instance of the model in the controller breaks the controller isolation. To follow the dependency injection patterns, those models can be injected into the controller just by requiring the model's in the controller's constructor function.

Let's see an example of a PostController that requires a Post model:

```php
class PostsController
{

    public function __construct (Posts $model) {
        $this->model = $model;
    }

}
```

You can make use of the injected model inside your controllers using `$this->model` like this:

```php
public function activePosts () {
    return $this->model->where('active', 1)->get();
}
```

---

## Base controller

If you want to have a solid fundation for your controllers, you can create them extending the default `Controller` class and requiring a model:

```php
class PostsController extends ApiFramework\Controller
{

    public function __construct (ApiFramework\App $app, Posts $model) {
        parent::__construct($app);
        $this->model = $model;
    }

}
```

- The first parameter in the constructor is the application instance, that is saved as `$this->app` by the parent constructor. It's required to give the controller access to the application goodies like `$this->app->request`, `$this->app->file` and all the others singletons.

- After that first parameter, you can request a model (in this case is `Posts`) that will be stored as `$this->model`.

That extended controller will inherit some handy default methods:

- **index** to return a listing of the resource
- **show** to return a single resource item
- **create** to save a new resource item in storage
- **update** to update a resource item in storage
- **destroy** to destroy a resource item in storage

As you can see, those are the expected methods names when you declare resource routes, so you can get a full bootstraped CRUD resource by combining the `PostController` controller, the injected `Posts` model and the `/posts` route.

```php
$app->router->resource('/posts', 'PostsController');
```

All methods (except `index`) receive the first wildcard found in the URL as the `$id` parameter.

```php
public function destroy ($id = null) { ... };
```

---

## Additional models

You can require more of one model in the controller's constructor, but the default CRUD actions inherited from the base controller (index, show, store, update, destroy) will always use `$this->model`, so be careful to choose other names for the additional models. For example:

```php
class PostsController extends ApiFramework\Controller
{

    public function __construct (ApiFramework\App $app, Posts $model, Tags $tags) {
        parent::__construct($app);
        $this->model = $model;
        $this->tags = $tags;
    }

}
```

---

## Responses and errors

In the most common scenario, your controllers will return and array that will be output by the application in the default format (JSON).

```php
public function send ($id) {
    ...
    return ['success' => true, 'id' => $id];
};
```

In case of errors, you can return early throwing an exception:

```php
public function invite ($age) {
    if ($age < 18) {
        throw new Exception('You must be older to get to the party', 401);
    }
    ...
};
```


