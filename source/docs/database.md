# Database

Most of your interaction with the database is handled by the models. But if you need to query the database directly, you can do it with the `Database` class. In your `Models`, `Controllers` or any other class extending from `Core`, you don't need to instanciate it: you can use the singleton `$this->app->db`. The method names follow the SQL function names, so - if you are familiar with SQL - you already know how to use it.

---

## Methods

#### Selects

** Use a table **

```
$this->app->db->table('users');
```

** Get records **

```
$this->app->db->table('users')->get();
```

** Get specific columns **

```
$this->app->db->table('users')->select(['id', 'name'])->get();
```

** Get records using a where condition **

```
$this->app->db->table('users')->where('active', 1)->get();
```

** Get records using a where condition **

```
$this->app->db->table('users')->where('active', 1)->get();
```

** Join with other table **

```
$this->app->db->table('users')->join('users_roles', 'users.id', '=', 'users_roles.user_id')->get();
```

** Count records **

```
$this->app->db->table('users')->count();
```

** Limit and offset **

```
$this->app->db->table('users')->limit(10)->offset(20)->get();
```

#### Inserts

** Insert a record **

```
$this->app->db->table('users')->insert(['username' => 'John', 'lastName' => 'Doe']);
```

** Insert a record and get its ID **

```
$this->app->db->table('users')->insertGetId(['username' => 'Walter', 'lastName' => 'White']);
```

#### Update

** Update a record **

```
$this->app->db->table('users')->where('id', 1)->update(['language' => 'fr']);
```

#### Delete

** Delete a record **

```
$this->app->db->table('users')->where('id', 1)->delete();
```

---

## Chaining methods

Every method returns the database instance, so you can chain them. For example, you can include many where conditions and a limit condition in any order:

```
$this->app->db->table('users')->where('active', 1)->limit(10)->where('age', 18, '>');
```

The TABLE, LIMIT, JOIN and WHERE settings are accumulable, but are reseted every time you perform a write action (`insert`, `update` or `delete`) or change the table in use (with `table`). So, if you made an insert, you have to specify the table name again.

```
$this->app->db->table('users')->insert(['name' => 'John']);
$this->app->db->table('users')->where('active', 0)->delete();
```










