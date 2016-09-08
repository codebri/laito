<?php

$url = 'http://laito.dev/source/';
$pages = [
    'index.html',
    'installation.html',
    'routing.html',
    'controllers.html',
    'models.html',
    'database.html',
    'request.html'
];

foreach ($pages as $page) {
    file_put_contents('../' . $page, file_get_contents($url . $page));
}