<?php

// Get config
include 'config.php';
$local = false;

// Generate index
file_put_contents('../index.html', file_get_contents($url . 'index.html'));

// Generate sections
foreach ($pages as $module) {
    if ($module['title'] !== 'Links') {
        foreach ($module['sections'] as $key => $page) {
            file_put_contents('../' . $key . '.html', file_get_contents($url . $key . '.html'));
        }
    }
}

// Message
echo "Done!\r\n";