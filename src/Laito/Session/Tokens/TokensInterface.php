<?php namespace Laito\Session\Tokens;

interface TokensInterface
{

    /**
     * Gets the token data
     *
     * @param string $hash Token hash
     * @return array|bool Token data or false
     */
    public function get ($hash = null);

    /**
     * Creates a token
     *
     * @param array $data Data to store
     * @return Token data
     */
    public function create ($data = []);

    /**
     * Updates a token
     *
     * @param string $hash Token hash
     * @param array $data Data to store
     * @return Token data
     */
    public function update ($hash = null, $data);

    /**
     * Destroys a token
     *
     * @param string $hash Token hash
     * @return bool Success or failure
     */
    public function destroy ($hash = null);

    /**
     * Creates a random token hash
     *
     * @return string Token hash
     */
    public function hash ();

}