<?php namespace Laito;

class Session extends Core
{

    /**
     * @var string Users table name
     */
    protected $table = 'users';

    /**
     * @var string Username column name
     */
    protected $usernameColumn = 'email';

    /**
     * @var string Password column name
     */
    protected $passwordColumn = 'password';

    /**
     * Attempts to start a session
     *
     * @param string $username Username to login
     * @param string $password Password
     * @param array $data Data to store in the session
     * @return array|bool Session data or false
     */
    public function login ($username, $password, $data) {

        // Verify the password against the stored hash
        if (!password_verify($password, $this->getStoredPassword($username))) {
            throw new \Exception('Incorrect username or password', 401);
        }

        // Create token
        return $this->app->tokens->create($data);
    }

    /**
     * Starts a session
     *
     * @param array $data Data to store in the session
     * @return string Token
     */
    public function forceLogin ($data) {
        return $this->app->tokens->create($data);
    }

    /**
     * Gets a session
     *
     * @param string $token Token hash
     * @return array|bool Session data or false
     */
    public function get ($token = null) {
        return $this->app->tokens->get($token);
    }

    /**
     * Gets a session field
     *
     * @param string $field Field name
     * @param string $token Token hash
     * @return array|bool Session data or false
     */
    public function getField ($field, $token = null) {
        $session = $this->app->tokens->get($token);
        return ($session && isset($session[$field]))? $session[$field] : null;
    }

    /**
     * Updates a session
     *
     * @param string $token Token hash
     * @param array $data Data to store
     * @return array|bool Session data or false
     */
    public function update ($token = null, $data = []) {
        return $this->app->tokens->update($token, $data);
    }

    /**
     * Removes a session
     *
     * @param string $token Token hash
     * @return boolean Success or fail of file delete
     */
    public function logout ($token = null) {
        return $this->app->tokens->destroy($token);
    }

    /**
     * Gets an stored password for a user
     *
     * @param string $username Username
     * @return mixed User password, of false if the user does not exist
     */
    private function getStoredPassword ($username) {

        // Get table object
        $db = $this->app->db->reset()->table($this->table);

        // Find user by username column
        $user = $db->select($this->passwordColumn)->limit(1)->where($this->usernameColumn, $username)->getOne();

        // Abort if the user or the password column do not exist
        if (!is_array($user) || !isset($user[$this->passwordColumn])) {
            return false;
        }

        // Return password column
        return $user[$this->passwordColumn];
    }
}