<?php namespace ApiFramework;

/**
 * Lang class
 *
 * @package default
 * @author Mangolabs
 */

class Lang extends Core
{

    /**
     * @var string Language key
     */
    protected $lang = null;

    /**
     * @var array Languages array
     */
    protected $languages = [];


    /**
     * Constructor
     * 
     * @param App $app Application instance
     */
    public function __construct (App $app) {
        parent::__construct($app);
        $this->lang = $this->app->config('lang.default');
    }


    /**
     * Loads a language
     *
     * @param string $lang Language key
     * @return boolean Success or fail of language load
     */
    public function load ($lang) {

        // Return if the language is already loaded
        if (isset($this->languages[$lang])) {
            return true;
        }

        // Abort if the language file does not exist
        $path = $this->app->config('lang.folder')  . $lang . '.json';
        if (!file_exists($path)) {
            return false;
        }

        // Add the terms to the internal dictionary
        $terms = file_get_contents($path);
        return $this->languages[$lang] = json_decode($terms, true);
    }


    /**
     * Gets a translated term
     *
     * @param string $term Term to be translated
     * @param string $lang Language key
     * @return string Translated term
     */
    public function get ($term, $lang = null) {

        // If we don't receive a language key, use the default
        $lang = isset($lang) ? $lang : $this->lang;

        // Load the language terms
        if (!isset($this->languages[$lang])) {
            $this->load($lang);
        }

        // Return the translated term
        return isset($this->languages[$lang][$term]) ? $this->languages[$lang][$term] : $term;
    }


    /**
     * Sets the default language
     * 
     * @param string $lang Language key
     * @return mixed Language key setted, or false if failed
     */
    public function locale ($lang) {

        // Check if the language can be loaded
        if (!$this->load($lang)) {
            return false;
        }

        // Set the new lang
        return $this->lang = $lang;
    }
}
