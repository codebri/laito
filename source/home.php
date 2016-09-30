<div class="site-header">
    <h2>
        Laito is a PHP microframework that helps you create quick and powerful REST APIs.
    </h2>
</div>

<div class="container">

    <h2 class="home-title">
        <span class="text-muted">&mdash;</span> Your code <span class="text-muted">&mdash;</span>
    </h2>

    <div class="app-window">

        <ul class="chrome-tabs-window-controls">
            <li class="window-close"></li>
            <li class="window-restore"></li>
            <li class="window-maximize"></li>
        </ul>

        <ul class="nav nav-tabs nav-chrome-tabs">
          <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#home">
                index.php
                <span>&times;</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#profile">
                PostsController.php
                <span>&times;</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#messages">
                Posts.php
                <span>&times;</span>
            </a>
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

    <?php if ($api && is_array($api['modules'])): ?>
        <?php foreach ($api['modules'] as $module): ?>
            <div class="row">
                <div class="col-md-12">
                    <h2 class="home-title">
                        <span class="text-muted">&mdash;</span> Your API <span class="text-muted">&mdash;</span>
                    </h2>
                    <div class="cards" id="<?=$module['module']?>">
                        <?php foreach ($module['calls'] as $key => $call): ?>
                            <div class="card" data-call="<?=$call['id']?>">
                                <div class="card-header collapsed" data-toggle="collapse" data-parent="#<?=$module['module']?>" data-target="#<?=$call['id']?>">
                                    <span class="tag tag-<?=$call['method']?>">
                                        <?=$call['method']?>
                                    </span>
                                    <code data-base="<?=$api['baseUrl']?>" data-url="<?=$call['url']?>"><?=$call['url']?></code>
                                </div>
                                <div id="<?=$call['id']?>" class="collapse">
                                    <div class="card-block">
                                        <form method="<?=$call['method']?>" action="<?=$api['baseUrl']?><?=$call['url']?>" target="_blank">
                                            <?php if (isset($call['urlParams'])): ?>
                                                <?php foreach ($call['urlParams'] as $param): ?>
                                                    <div class="form-group">
                                                        <label class="strong"><?=$param['name']?></label>
                                                        <input type="<?php if (isset($param['type'])): ?><?=$param['type']?><?php endif; ?>" name="<?=$param['name']?>" class="form-control" placeholder="<?=$param['name']?>" data-urlParam="<?=$param['name']?>" autocomplete="off">
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            <?php if (isset($call['params'])): ?>
                                                <?php foreach ($call['params'] as $param): ?>
                                                    <div class="form-group">
                                                        <label class="strong"><?=$param['name']?></label>
                                                        <input type="<?php if (isset($param['type'])): ?><?=$param['type']?><?php endif; ?>" name="<?=$param['name']?>" class="form-control" placeholder="<?=$param['name']?>" autocomplete="off">
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            <button type="submit" class="btn btn-primary" data-loading-text="Loading...">Submit</button>
                                            <div class="result">
                                                <div>
                                                    <p>
                                                        <i class="fa text-muted status-icon"></i> <span class="status"></span>
                                                    </p>
                                                </div>
                                                <pre class="plain"></pre>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif;?>

    <div class="get-started">
        <a href="installation.html" class="btn btn-l btn-primary">
            Get started
        </a>
    </div>

</div>