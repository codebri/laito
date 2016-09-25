# Request

You may to need to obtain data from the query string of a GET call, or the parameters of a POST request. You can do it trought the `Request` class. You don't have to instantiate it, is available trought `$this->app->request`. So you can use it in any controller class:

```
class UsersController extends Laito\Controller
{
    public function index ($filters = []) {
        $inputs = $this->app->request->input();
        ...
    }
}
```

---

## Methods

** Get all inputs **

```
$this->app->request->input();
```

** Get a single input **

```
$this->app->request->input('age');
```
```

** Check if an input is defined **

```
$this->app->request->hasInput('age');
```

** Get a single input with a default value **

```
$this->app->request->input('page', 'home');
```

** Get the requested URL **

```
$this->app->request->url();
```

** Get the access token **

```
$this->app->request->token();
```
