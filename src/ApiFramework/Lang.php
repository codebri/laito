<?php

namespace ApiFramework;

/**
 * Language
 *
 * Translates terms
 * @version 1.0
 * @package Lang
*/
class Lang extends Core
{

    private static $locale = 'en_en';
    private static $languages = [];

    /**
     * Loads a language list of terms.
     *
     * @param string $key
     * @return string
     */
    public static function load ($lang)
    {
        // Build the language file path
        $file = CONFIG_DOCUMENT_ROOT . 'config/lang/' . $lang . '.json';

        // Abort if the language is already loaded or its file does not exist
        if (self::$languages[$lang] || !file_exists($file)) {
            return false;
        }

        // Add the language terms to the internal dictionary
        $terms = file_get_contents($file);
        return self::$languages[$lang] = json_decode($terms, true);
    }


    /**
     * Gets a translated term.
     *
     * @param string $key
     * @param string $lang
     * @return string
     */
    public static function get ($key, $lang = null)
    {
        // Set the default language
        if (!$lang) {
            $lang = self::$locale;
        }

        // Load the language terms
        if (!self::$languages[$lang]) {
            self::load($lang);
        }

        // Return the translated term
        return self::$languages[$lang][$key] ?: $key;
    }


    /**
     * Sets the default language.
     * 
     * @param integer $limit
     * @return boolean
     */
    public static function setLocale ($lang)
    {
        // Check if the language can be loaded
        if (!self::load($lang)) {
            return false;
        }

        // Set the new locale
        return self::$locale = $lang;
    }
}
