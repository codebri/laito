<?php

// Show errors
error_reporting(E_ALL | E_ERROR | E_PARSE);

// Load dependencies
require_once __DIR__ . '/vendor/autoload.php';

// Instance parser
$parser = new \cebe\markdown\GithubMarkdown();

// Get route
$route = (filter_input(INPUT_GET, 'route') !== null)? filter_input(INPUT_GET, 'route') : 'index.html';
$route = str_replace('.html', '', $route);

// Get markdown file
if (isset($route) && ($route !== '') && file_exists('docs/' . $route . '.md')) {
    $markdown = file_get_contents('docs/' . $route . '.md');
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
<link href='//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css' rel='stylesheet'>

<!-- Font Awesome -->
<link href='//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css' rel='stylesheet'>

<!-- Site -->
<style type="text/css">

    a, a:hover, a:active, a:focus {
        text-decoration: none;
    }

    table {
        width: 100%;
        border: 1px solid #DDD;
        margin-top: 20px;
    }

    table th {
        background: #EEE;
        border-bottom: 1px solid #DDD;
    }

    table td, table th {
        padding: 10px;
    }

</style>

</head>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1 class="page-header">
                Laito
            </h1>
        </div>
    </div>
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

</body>
</html>