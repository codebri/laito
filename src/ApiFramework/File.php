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
     * @param $path File path
     * @return string File content
     */
    public function get ($path) {

        // Abort if the file does not exists
        if (!$path || !file_exists($path) || !is_readable($path)) {
            return false;
        }

        // Get the file contents
        $contents = file_get_contents($path);

        // Return the contents
        return $contents;
    }

    /**
     * Save contents to a file
     *
     * @param $path File path
     * @param $path Contents
     * @return int|bol Number of bytes written, of false on failure
     */
    public function put ($path, $contents) {

        // Abort if the path is not defined
        if (!$path) {
            return false;
        }

        // Save the contents to the file
        return file_put_contents($path, $contents);
    }

    /**
     * Appends contents to a file
     *
     * @param $path File path
     * @param $path Contents
     * @return int|bool Number of bytes written, of false on failure
     */
    public function append ($path, $contents) {

        // Abort if the path is not defined
        if (!$path || !file_exists($path)) {
            return false;
        }

        // Append the contents
        return file_put_contents($path, $contents, FILE_APPEND);
    }

    /**
     * Deletes a file
     *
     * @param $path File path
     * @return int|bool Number of bytes written, of false on failure
     */
    public function delete ($path) {

        // Abort if the path is not defined
        if (!$path || !file_exists($path)) {
            return false;
        }

        // Destroy the file
        return unlink($path);
    }

    /**
     * Copies a file
     *
     * @param $path File path
     * @param $target Target path
     * @return boolean Success or failure
     */
    public function copy ($path, $target) {

        // Abort if the path or the target are not defined
        if (!$path || !file_exists($path) || !$target) {
            return false;
        }

        // Copy the file to the new location
        return copy($path, $target);
    }

    /**
     * Moves a file
     *
     * @param $path File path
     * @param $target Target path
     * @return boolean Success or failure
     */
    public function move ($path, $target) {

        // Abort if the path or the target are not defined
        if (!$path || !file_exists($path) || !$target) {
            return false;
        }

        // Move the file to the new location
        return rename($path, $target);
    }

    /**
     * Gets a file extension
     *
     * @param $path File path
     * @return string File extension
     */
    public function extension ($path) {

        // Abort if the path or the target are not defined
        if (!$path || !file_exists($path)) {
            return false;
        }

        // Return extension
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Gets a file size
     *
     * @param $path File path
     * @return boolean File size in bytes, or false on failure
     */
    public function size ($path) {

        // Abort if the path or the target are not defined
        if (!$path || !file_exists($path)) {
            return false;
        }

        // Return extension
        return filesize($path);
    }

}