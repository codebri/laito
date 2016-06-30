<?php namespace Laito;

/**
 * Authentication class
 *
 * @package default
 * @author Mangolabs
 */

class Authentication extends Model
{

    /**
     * @var Array Authenticated user
     */
    protected $user = null;

    /**
     * @var Array Current token
     */
    protected $token = null;

    /**
     * @var string Username column in table
     */
    protected $usernameColumn = 'email';

    /**
     * @var string Password column in table
     */
    protected $passwordColumn = 'password';

    /**
     * @var Array TTL for reminder
     */
    protected $ttl = ['reminder' => 3600];

    /**
     * @var Array Folder paths for tokens and reminders
     */
    protected $folders = ['token' => 'storage/sessions', 'reminder' => 'storage/reminders'];

    /**
     * @var string Reminders suffix
     */
    protected $remindersSuffix = 'reminders_';

    /**
     * @var array Fields to be store in the session file
     */
    protected $sessionFields = [
        'id',
        'username',
        'email'
    ];

    /**
     * Attempts to login a user
     *
     * @param string $username Username to login
     * @param string $password Password
     * @return string Token
     */
    public function attempt ($username, $password) {

        // Check credentials
        if (!$this->validate($username, $password)) {
            throw new \Exception('Incorrect username or password', 401);
        }

        // Logins user
        $token = $this->startSession($username);

        // Return token
        return $token;
    }

    /**
     * Logins a user
     *
     * @param string $username Username to login
     * @return mixed
     */
    public function startSession ($username) {

        // Store session file
        $token = $this->createTokenHash($username);

        // Get user data
        $user = $this->findByUsername($username);

        // Abort if the user does not exist
        if (!$user) {
            throw new \Exception('Username not found', 404);
        }

        // Manage session data
        unset($user[$this->passwordColumn]);
        $this->user = $user;
        $this->token = $token;

        // Save session
        $sessionSaved = $this->storeSession($token, $user);

        // Return token
        return $token;
    }

    /**
     * Tells if an user is logged in
     *
     * @param string $token Token
     * @return mixed User session data or false
     */
    public function check ($token) {

        // Check session
        $sessionData = $this->getSession($token);
        if (!$sessionData) {
            return false;
        }

        // Return user info
        return $sessionData;
    }

    /**
     * Signs out a user
     *
     * @param string $token Token
     * @return boolean Success or fail of sign out
     */
    public function signout ($token) {

        // Get session
        $sessionData = $this->getSession($token);

        // Delete session cookies
        $sessionDeleted = $this->deleteSession($token);

        // Return success or fail
        return $sessionDeleted;
    }

    /**
     * Validates a user - password pair
     *
     * @param string $username Username to validate
     * @param string $password Raw password
     * @return boolean Success or fail of validation
     */
    public function validate ($username, $password) {

        // Get the user's data
        $storedPassword = $this->getStoredPassword($username);

        // Verify the password against the stored hash
        return password_verify($password, $storedPassword);
    }

    /**
     * Creates a reminder and sends it to the user
     *
     * @param string $username Username to validate
     * @return boolean|string Password reminder or false
     */
    public function remindPassword ($username) {

        // Get the user's data
        $user = $this->findByUsername($username);

        // Abort if the user does not exist
        if (!$user) {
            throw new \Exception('Username not found', 404);
        }

        // Creates the reminder
        $reminder = $this->createReminderHash($username);

        // Saves the reminder
        $reminderSaved = $this->storeReminder($reminder, [$this->usernameColumn => $username]);

        // Return
        return $reminder;
    }

    /**
     * Changes the password of a user
     *
     * @param string $username Username
     * @param string $token Token or password reminder
     * @param string $newPassword New password
     * @return boolean Success or fail of password change
     */
    public function changePassword ($username, $token, $newPassword) {

        // Get the user's data
        $user = $this->findByUsername($username);

        // Abort if the user does not exist
        if (!$user) {
            throw new \Exception('Username not found', 404);
        }

        // Get data from reminder or session
        $isReminder = $this->isReminder($token);
        $sessionData = $isReminder? $this->getReminder($token) : $this->getSession($token);

        // Abort if the session is invalid
        if (!$sessionData) {
            $type = $isReminder? 'reminder' : 'token';
            throw new \InvalidArgumentException('Invalid ' . $type, 400);
        }

        // Abort if the received username does not match the session data username
        if ($username !== $sessionData['user'][$this->usernameColumn]) {
            throw new \InvalidArgumentException('Invalid username for this ' . $type, 400);
        }

        // Abort if the reminder is expired
        $maxage = time() + $this->ttl['reminder'];
        if ($isReminder && (!isset($sessionData['expires']) || $sessionData['expires'] > $maxage)) {
            throw new \InvalidArgumentException('Expired reminder', 400);
        }

        // Hash password
        $data['password'] = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update user
        $userUpdated = $this->update($sessionData['user']['id'], $data);

        // Check for errors
        if (!$userUpdated) {
            throw new \InvalidArgumentException('Could not change password', 500);
        }

        // Delete reminder
        if ($userUpdated && $isReminder) {
            $reminderDeleted = $this->deleteReminder($token);
        }

        // Return status
        return $userUpdated;
    }

    /**
     * Retrieves data from the session file
     *
     * @param string $token Token
     * @return mixed Session data, or false if the session is invalid
     */
    public function getSession ($token = null) {

        // Set token
        if (!$token) {
            $token = $this->token;
        }
        if (!$token) {
            return false;
        }

        // Determine token path
        $path = $this->sessionPath($token);

        // Abort if the session does not exist
        if (!file_exists($path)) {
            return false;
        }

        // Get session data
        $sessionData = json_decode(file_get_contents($path), true);

        // Return session
        return $sessionData;
    }

    /**
     * Retrieves the logged in ID
     *
     * @param string $token Token
     * @return mixed Logged user ID, or false if the session is invalid
     */
    public function getId ($token) {
        $session = $this->getSession($token);
        if (!$session) {
            return false;
        }
        return $session['id'];
    }

    /**
     * Gets a user from the database
     *
     * @param string $username Username
     * @return mixed User array, of false if the user does not exist
     */
    private function findByUsername ($username) {
        $user = $this->db()->reset()->table($this->table)->select($this->sessionFields)->limit(1)->where($this->usernameColumn, $username)->getOne();
        return $user;
    }

    /**
     * Gets an stored password for a user
     *
     * @param string $username Username
     * @return mixed User password, of false if the user does not exist
     */
    private function getStoredPassword ($username) {
        $user = $this->db()->reset()->table($this->table)->select($user[$this->passwordColumn])->limit(1)->where($this->usernameColumn, $username)->getOne();
        if (is_array($user) && isset($user[$this->passwordColumn])) {
            return $user[$this->passwordColumn];
        }
        return false;
    }

    /**
     * Creates a random token hash based on the username and time
     *
     * @param string $username Username to hash
     * @return string Token
     */
    private function createTokenHash ($username) {
        return md5($username . time() . rand(0, 100));
    }

    /**
     * Returns the session path for a given token
     *
     * @param string $token Token
     * @return string Session path
     */
    private function sessionPath ($token) {
        return $this->folders['token'] . '/' . $token . '.json';
    }

    /**
     * Stores the session data on a file
     *
     * @param string $token Token
     * @param string $data Data to store
     * @return boolean Success or fail of file writing
     */
    private function storeSession ($token, $data) {
        $path = $this->sessionPath($token);
        $data['token'] = $token;
        $data['ctime'] = time();
        return file_put_contents($path, json_encode($data));
    }

    /**
     * Deletes the session file
     *
     * @param string $token Token
     * @return boolean Success or fail of file delete
     */
    private function deleteSession ($token) {
        $path = $this->sessionPath($token);
        if (file_exists($path) && is_writable($path)) {
            unlink($path);
        }
        return true;
    }

    /**
     * Creates a random reminder hash on the username and time
     *
     * @param string $username Username to hash
     * @return string Reminder
     */
    private function createReminderHash ($username) {
        return $this->remindersSuffix . md5($username . time() . rand(0, 100));
    }

    /**
     * Returns the reminder path for a given reminder
     *
     * @param string $reminder Reminder
     * @return string Reminder path
     */
    private function reminderPath ($reminder) {
        return $this->folders['reminder'] . '/' . $reminder . '.json';
    }

    /**
     * Stores the reminder data on a file
     *
     * @param string $reminder Reminder
     * @param string $data Data to store
     * @return boolean Success or fail of file writing
     */
    private function storeReminder ($reminder, $data) {
        $path = $this->reminderPath($reminder);
        return file_put_contents($path, json_encode([
            'user' => $data,
            'reminder' => $reminder,
            'expires' => time() + $this->ttl['reminder']
        ]));
    }

    /**
     * Retrieves the data from the reminder file
     *
     * @param string $reminder Reminder
     * @return mixed Reminder data, or false if the reminder is invalid
     */
    private function getReminder ($reminder) {
        $path = $this->reminderPath($reminder);
        return (file_exists($path))? json_decode(file_get_contents($path), true) : false;
    }

    /**
     * Deletes the reminder file
     *
     * @param string $reminder Reminder
     * @return boolean Success or fail of file delete
     */
    private function deleteReminder ($reminder) {
        $path = $this->reminderPath($reminder);
        return unlink($path);
    }

    /**
     * Check if a hash is a reminder
     *
     * @param string $string String to evaluate
     * @return boolean True if the string is a reminder
     */
    private function isReminder ($string) {
        return strpos($string, $this->remindersSuffix) === 0;
    }

}