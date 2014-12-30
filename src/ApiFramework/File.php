<?php namespace ApiFramework;

/**
 * File class
 *
 * @package default
 * @author Mangolabs
 */

class File extends Core
{

    /**
     * Get the contents of a file
     *
     * @return string File content
     */
    public function get ($path) {

        // Abort if the file does not exists
        if (!file_exists($path) || !is_readable($path)) {
            return false;
        }

        // Get the file contents
        $contents = file_get_contents($path);

        // Return the contents
        return $contents;
    }

}