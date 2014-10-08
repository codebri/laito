<?php

namespace ApiFramework;

/**
 * User
 *
 * Manages users and sessions
 * @version 1.0
 * @package User
*/
class User extends BaseModule
{
    protected $table = 'users';
    protected $validFilters = ['email' => 'users.email'];
    private $sessionsFolder = 'storage/sessions/';
    private $usernameColumn = 'email';
    private $passwordColumn = 'password';

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
        if (!$this->isValid($user, $password)) {
            return $this->error(401, 'Invalid user');
        }

        // Store session file
        $token = md5(time());
        file_put_contents($this->getSessionsPath() . $token . '.json', json_encode(['user' => $user]));

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
        $sessionFile = $this->getSessionsPath() . $token . '.json';
        if (!file_exists($sessionFile)) {
            return $this->error(401, 'Invalid token');
        }

        // Get session data
        $session = json_decode(file_get_contents($this->getSessionsPath() . $token . '.json'), true);

        // Check session user
        if (!$session['user']) {
            return $this->error(401, 'Invalid token');
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
        $session = json_decode(file_get_contents($this->getSessionsPath() . $token . '.json'), true);

        // Check session
        if (!$session['user']) {
            return $this->error(401, 'Invalid token');
        }

        // Delete session file
        unlink($this->getSessionsPath() . $token . '.json');

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
        $userData = $this->where($this->usernameColumn, $user)->first();
        if (!$userData) {
            return false;
        }
        return password_verify($password, $userData[$this->passwordColumn]);
    }

    /**
     * Get the session's folder path
     *
     * @return string
     */
    public function getSessionsPath ()
    {
        return CONFIG_DOCUMENT_ROOT . $this->sessionsFolder;
    }

}
