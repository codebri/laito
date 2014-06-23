<?php

namespace ApiFramework;

/**
 * User
 *
 * Manages users and sessions
 * @author Nico Munoz <nicolas.munoz@loogares.com>
 * @version 1.0
 * @package User
*/
class User extends BaseModule
{

    private static $user = 'guest';
    private static $password = '1a1dc91c907325c69271ddf0c944bc72';
    private static $sessionsFolder = 'storage/sessions/';

    /**
     * Login a user.
     *
     * @param string $key
     * @return mixed
     */
    public function login ()
    {
        // Get credentials
        $user = Request::input('user');
        $password = Request::input('password');

        // Check credentials
        if (!self::isValid($user, $password)) {
            return self::error(401, 'Invalid user');
        }

        // Store session file
        $token = md5(time());
        file_put_contents(self::getSessionsPath() . $token . '.json', json_encode(['user' => $user]));

        // Set session cookies
        setcookie('user', $user, time() + 3600, '/');
        setcookie('token', $token, time() + 3600, '/');

        // Return token
        return ['user' => $user, 'token' => $token];
    }

    /**
     * Retrieve the logged user info.
     *
     * @param string $key
     * @return mixed
     */
    public function info ($token)
    {
        // Get token
        $token = ($token)?: Request::token();

        // Check session file
        $sessionFile = self::getSessionsPath() . $token . '.json';
        if (!file_exists($sessionFile)) {
            return self::error(401, 'Invalid token');
        }

        // Get session data
        $session = json_decode(file_get_contents(self::getSessionsPath() . $token . '.json'), true);

        // Check session user
        if (!$session['user']) {
            return self::error(401, 'Invalid token');
        }

        // Return user info
        return ['user' => $session['user'], 'token' => $token];
    }

    /**
     * Logout a user.
     *
     * @param string $key
     * @return mixed
     */
    public function logout ($token)
    {
        // Get session
        $token = ($token)?: Request::token();
        $session = json_decode(file_get_contents(self::getSessionsPath() . $token . '.json'), true);

        // Check session
        if (!$session['user']) {
            return self::error(401, 'Invalid token');
        }

        // Delete session file
        unlink(self::getSessionsPath() . $token . '.json');

        // Delete session cookies
        setcookie('user', '', time() - 3600, '/');
        setcookie('token', '', time() - 3600, '/');

        // Return response
        return true;
    }

    /**
     * Validates a user - password pair.
     *
     * @param string $key
     * @return mixed
     */
    public function isValid ($user, $password)
    {
        if (($user !== self::$user) || (md5($password) !== self::$password)) {
            return false;
        }
        return true;
    }

    /**
     * Get the session's folder path
     *
     * @return string
     */
    private function getSessionsPath ()
    {
        return CONFIG_DOCUMENT_ROOT . self::$sessionsFolder;
    }

}
