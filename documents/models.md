# Models

The models are the classes that manage a certain resource. Following the MVC pattern, the models are the only ones that should talk to the storage system in your application.

The storage may vary. For example, you can have some logs in a text file and access them like this:

```
class Logs
{
    function get () {
        return file_get_contents('./logs.txt');
    }
}
```

---

## Base model

But, in the most common scenario, you'll have the records in a database and some common methods to list, create, update and destroy records.

That's where the base model enters in action. You can easily create your models by extending the default `Model` class and defining some basic configuration:

```
class Posts extends Laito\Model
{

    /**
     * @var string Table name
     */
    protected $table = 'posts';

    /**
     * @var array Readable columns
     */
    protected $columns = ['id', 'name', 'text', 'date'];

    /**
     * @var array Writable columns
     */
    protected $fillable = ['name', 'text'];

}
```

And that's all! Now you can use your model as follows.

---

## Methods

**Get a list of posts**

```
$model->get();
```

**Get a list of active posts**

```
$model->where('active', 1)->get();
```

**Get a single post by ID**

```
$model->find(4);
```

**Create a new post**

```
$model->create(['name' => 'Hello, World!', 'text' => 'Some nice text']);
```

**Update a post by ID**

```
$model->update(4, ['name' => 'New title', 'text' => 'New text']);
```

**Delete a post by ID**

```
$model->destroy(28);
```

---

## Model relationships

When your model is related to others, you can declare a model relationship in the `$relationships` array.

You can define relationships of various types:

** One to one **

```
protected $relationships = [
    'hasOne' => [
        [
            'table' => 'roles',
            'localKey' => 'role_id',
            'foreignKey' => 'id',
            'columns' => ['id', 'name'],
            'alias' => 'role'
        ]
    ]
];
```

** One to many **

```
protected $relationships = [
    'hasMany' => [
        [
            'table' => 'messages',
            'localKey' => 'id',
            'foreignKey' => 'user_id',
            'columns' => ['text', 'public'],
            'alias' => 'messages',
            'limit' => 100,
            'sync' => ['insert', 'update'] // false, 'insert', 'update', 'overwrite'
        ]
    ]
];
```

** Many to many **

```
protected $relationships = [
    'belongsToMany' => [
        [
            'table' => 'permissions',
            'pivot' => 'user_permission',
            'localKey' => 'user_id',
            'foreignKey' => 'permission_id',
            'alias' => 'permissions',
            'sync' => true // true, false
        ]
    ]
];
```

Of course, if a model have more than one relationship you can define them all at once.

---

## Overriding base controller methods

You can extend of override the default methods. For example, if you want to process a list of results before returning them:

```
class Posts extends Laito\Model
{

    protected $table = 'posts';

    // Override get method
    public function get () {

        // Call parent
        $records = parent::get();

        // Iterate and process records
        $records = array_map(function ($element) {
            $element['settings'] = json_decode($element['settings'], true);
            return $element;
        }, $records);

        // Return the new set
        return $records;
    }

}

```