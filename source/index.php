<?php

// Show errors
error_reporting(E_ALL | E_ERROR | E_PARSE);

// Load dependencies
require 'config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Instance parser
$parser = new \cebe\markdown\GithubMarkdown();

// Check if is local
$local = false;

// Get route
$route = (filter_input(INPUT_GET, 'route') !== null)? filter_input(INPUT_GET, 'route') : 'index.html';
$route = str_replace('.html', '', $route);

// Get markdown file
if (isset($route) && ($route !== '') && file_exists('../documents/' . $route . '.md')) {
    $markdown = file_get_contents('../documents/' . $route . '.md');
}

// Render page
$html = $parser->parse($markdown);

?>
<!DOCTYPE HTML>
<html lang='en-US'>
<head>
<meta charset='utf-8'>

<meta http-equiv='X-UA-Compatible' content='IE=edge'>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=no'>

<title>Laito</title>

<!-- Bootstrap -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.4/css/bootstrap.min.css" integrity="sha384-2hfp1SzUoho7/TsGGGDaFdsuuDL0LX2hnUp6VkX3CUQ2K4K+xjboZdsXyp4oUHZj" crossorigin="anonymous">

<!-- Font Awesome -->
<link href='//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css' rel='stylesheet'>

<!-- Google Fonts -->
<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700|Source+Sans+Pro:400,300' rel='stylesheet' type='text/css'>

<!-- Syntax Highlighter -->
<link rel="stylesheet" href="//highlightjs.org/static/demo/styles/github-gist.css">

<!-- Site -->
<link href='<?php if ($local): ?>../<?php endif; ?>assets/css/style.css' rel='stylesheet'>

</head>

<div class="site-header">
    <h1>
        Laito
    </h1>
    <h2>
        Powerful PHP REST APIs in a breeze
    </h2>
</div>

<div class="container">
    <?php if ($route === 'index'): ?>
        <div class="content">
            <?=$html?>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-3">
                <?php include 'sidebar.php' ?>
            </div>
            <div class="col-md-9">
                <div class="content">
                    <?=$html?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<footer>
    <div class="container">
        <hr>
        <p>Laito by <a href="http://codebri.com">Codebri</a></p>
    </div>
</footer>

<!-- Bootstrap -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js" integrity="sha384-THPy051/pYDQGanwU6poAc/hOdQxjnOEXzbT+OuUAFqNqFjL+4IGLBgCJC3ZOShY" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.2.0/js/tether.min.js" integrity="sha384-Plbmg8JY28KFelvJVai01l8WyZzrYWG825m+cZ0eDDS1f7d/js6ikvy1+X+guPIB" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.4/js/bootstrap.min.js" integrity="sha384-VjEeINv9OSwtWFLAtmc4JCtEJXXBub00gtSnszmspDLCtC0I4z4nqz7rEFbIZLLU" crossorigin="anonymous"></script>

<!-- Syntax Highlighter -->
<script src="//highlightjs.org/static/highlight.pack.js"></script>

<!-- Site -->
<script src="<?php if ($local): ?>../<?php endif; ?>assets/js/site.js"></script>

</body>
</html>