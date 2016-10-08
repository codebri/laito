<?php
namespace Laito\Session\Tokens;

use Laito\App;
use Laito\Core\Base;
use Laito\Session\Tokens\TokensInterface;

class FileTokens extends Base implements TokensInterface
{

    /**
     * Class constructor
     *
     * @param App $app App instance
     */
    public function __construct(App $app)
    {
        // Construct from parent
        parent::__construct($app);

        // Check if the tokens folder is writable
        if (!is_writable($this->app->config('tokens.storage'))) {
            throw new \Exception("The tokens storage is not writable", 500);
        }
    }

    /**
     * Gets the token data
     *
     * @param string $hash Token hash
     * @return array|bool Token data or false
     */
    public function get($hash = null)
    {
        // If the hash is not received, get it from the request
        $hash = $hash?: $this->app->request->token();

        // Determine token path
        $path = $this->path($hash);

        // Abort if the session does not exist
        if (!file_exists($path)) {
            return false;
        }

        // Update token file modification time
        touch($path);

        // Get session data
        $data = json_decode(file_get_contents($path), true);

        // Return session
        return $data;
    }

    /**
     * Creates a token
     *
     * @param array $data Data to store
     * @return Token data
     */
    public function create($data = [])
    {
        // Create a random hash
        $hash = $this->hash();

        // Store the file in the tokens storage
        $data = array_merge($data, ['token' => $hash, 'timestamp' => time()]);
        $stored = file_put_contents($this->path($hash), json_encode($data));

        // Return the data or false in case of failure
        return ($stored)? $data : false;
    }

    /**
     * Updates a token
     *
     * @param string $hash Token hash
     * @param array $data Data to store
     * @return Token data
     */
    public function update($hash = null, $data)
    {
        // If the hash is not received, get it from the request
        $hash = $hash?: $this->app->request->token();

        // Update the file in the tokens storage
        $token = $this->get($hash);
        $data = array_merge_recursive($token, $data);
        $stored = file_put_contents($this->path($hash), json_encode($data));

        // Return the data or false in case of failure
        return ($stored)? $data : false;
    }

    /**
     * Destroys a token
     *
     * @param string $hash Token hash
     * @return bool Success or failure
     */
    public function destroy($hash = null)
    {
        // If the hash is not received, get it from the request
        $hash = $hash?: $this->app->request->token();

        // Abort if the file does not exist
        $path = $this->path($hash);
        if (!file_exists($path)) {
            return true;
        }

        // Otherwise, remove the token file
        $success = unlink($path);
        if (!$success) {
            throw new Exception("The session could not be erased", 500);
        }

        // Return success
        return $success;
    }

    /**
     * Creates a random token hash
     *
     * @return string Token hash
     */
    public function hash()
    {
        return md5(time() . rand(0, pow(10, 10)));
    }

    /**
     * Returns the session path for a given token
     *
     * @param string $hash Token
     * @return string Session path
     */
    private function path($hash)
    {
        return $this->app->config('tokens.storage') . '/' . $hash . '.json';
    }

}