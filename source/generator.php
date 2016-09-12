<?php

include 'config.php';

foreach ($pages as $page) {
    echo file_put_contents('../' . $page . '.html', file_get_contents($url . $page . '.html'));
}