<?php

// Show errors
error_reporting(E_ALL | E_ERROR | E_PARSE);

// Load dependencies
require 'config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Instance parser
$parser = new \cebe\markdown\GithubMarkdown();

// Check if is local
$local = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1')? 1 : 0;

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

<!-- Twitter Bootstrap -->
<link href='//netdna.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' rel='stylesheet'>

<!-- Font Awesome -->
<link href='//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css' rel='stylesheet'>

<!-- Google Fonts -->
<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700|Source+Sans+Pro:400,300' rel='stylesheet' type='text/css'>

<!-- Syntax Highlighter -->
<link rel="stylesheet" href="//highlightjs.org/static/demo/styles/github.css">

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
    <div class="row">
        <div class="col-md-2">
            <?php include 'sidebar.php' ?>
        </div>
        <div class="col-md-10">
            <?=$html?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <hr>
            <p>Laito by <a href="http://codebri.com">Codebri</a></p>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src='//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'></script>

<!-- Bootstrap -->
<script src='//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js'></script>

<!-- Syntax Highlighter -->
<script src="//highlightjs.org/static/highlight.pack.js"></script>

<!-- Site -->
<script src="<?php if ($local): ?>../<?php endif; ?>assets/js/site.js"></script>

</body>
</html>