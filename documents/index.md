
<div class="text-md-center">
    <p class="lead">
        Laito is a PHP microframework for creating quick and powerful REST APIs.
    </p>
</div>

<div class="app-window">

    <ul class="chrome-tabs-window-controls">
        <li class="window-close"></li>
        <li class="window-restore"></li>
        <li class="window-maximize"></li>
    </ul>

    <ul class="nav nav-tabs nav-chrome-tabs">
      <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#home">index.php</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#profile">PostsController.php</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#messages">Posts.php</a>
      </li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane active" id="home">
<pre><code class="php">&lt;?php

// Require the package
require(__DIR__ . '/vendor/autoload.php');

// Create a new app
$app = new Laito\App([
  'database.type' => 'mysql',
  'database.server' => 'localhost',
  'database.name' => 'app',
  'database.username' => 'root',
  'database.password' => 'root'
]);

// Register your routes
$app->router->resource('/posts', 'PostsController');

// Start listening requests
$app->run();</code></pre>
      </div>
      <div class="tab-pane" id="profile">
<pre><code class="php">&lt;?php

class PostsController extends Laito\Controller
{

    /**
     * @var string Model name
     */
    public $modelName = 'Posts';

}</code></pre>
      </div>
      <div class="tab-pane" id="messages">
<pre><code class="php">&lt;?php

class Posts extends Laito\Model
{

    /**
     * @var string Table name
     */
    protected $table = 'posts';

    /**
     * @var array Readable columns
     */
    protected $columns = ['id', 'title', 'body', 'date'];

    /**
     * @var array Writable columns
     */
    protected $fillable = ['title', 'body'];

    /**
     * @var array Relationships
     */
    protected $relationships = [
        'hasMany' => [
            [
                'table' => 'comments',
                'localKey' => 'id',
                'foreignKey' => 'post_id',
                'columns' => ['comment', 'author'],
                'alias' => 'comments',
                'limit' => 100,
                'sync' => ['insert', 'update']
            ]
        ]
    ];

}</code></pre>
      </div>
    </div>
</div>

<div class="text-md-center">
  <a href="installation" class="btn btn-primary btn-lg">
    Get started
  </a>
</div>