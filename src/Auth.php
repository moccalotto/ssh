<?php

namespace Moccalotto\Ssh;

use UnexpectedValueException;

class Auth implements Contracts\AuthenticatorContract
{
    /**
     * Authentication method.
     *
     * the function name of the ssh2 auth method.
     * e.g. ssh_auth_pubkey_file
     *
     * @var string
     */
    protected $method;

    /**
     * Additional params for the ssh2_auth_* call
     * First agument must be the ssh2 session resource.
     * The rest of the arguments are contained in this variable.
     *
     * @var array
     */
    protected $params;

    protected function __construct($method, array $params)
    {
        $this->method = $method;
        $this->params = $params;
    }

    /**
     * Create password authentication object.
     *
     * Used when you log in only using username and password.
     *
     * @param string $username
     * @param string $password
     *
     * @return Auth
     */
    public static function viaPassword($username, $password)
    {
        return new static('ssh2_auth_password', [
            $username,
            $password,
        ]);
    }

    /**
     * Create key-file authentication object.
     *
     * Used when you log in using a specific private key.
     *
     * @param string $username
     * @param string $pubkeyfile  The path of the public key file (i.e. id_rsa.pub)
     * @param string $privkeyfile The path of the private key file (i.e. id_rsa)
     * @param string $passphrase  The passphrase that is used to encrypt the private key.
     *
     * @return Auth
     */
    public static function viaKeyFile($username, $pubkeyfile, $privkeyfile, $passphrase = null)
    {
        return new static('ssh2_auth_pubkey_file', [
            $username,
            $pubkeyfile,
            $privkeyfile,
            $passphrase,
        ]);
    }

    /**
     * Create agent-based authentication object.
     *
     * Used when you log in using the OS's SSH agent.
     * If your private key is encrypted, the SSH agent will prompt you for a password,
     * so this auth scheme works best in CLI applications.
     *
     * @param string $username
     *
     * @return Auth;
     */
    public static function viaAgent($username)
    {
        return new static('ssh2_auth_agent', [$username]);
    }

    /**
     * Authenticate an SSH session resource.
     *
     * @internal
     *
     * @return bool
     *
     * @throws UnexpectedValueException if $resource is not a valid SSH2 session resource.
     */
    public function authenticateSessionResource($resource)
    {
        if (!Valid::sshSessionResource($resource)) {
            throw new UnexpectedValueException('Parameter must be a valid SSH2 Session resource');
        }

        $args = $this->params;
        array_unshift($args, $resource);

        return call_user_func_array($this->method, $args);
    }
}
