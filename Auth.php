<?php

namespace Moccalotto\Ssh;

use UnexpectedValueException;

class Auth implements Contract\AuthenticatorContract
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
     * The rest of the arguments are contained in this variable
     */
    protected $params;

    protected function __construct($method, array $params)
    {
        $this->method = $method;
        $this->params = $params;
    }

    /**
     * Create password authentication object.
     */
    public static function viaPassword($username, $password)
    {
        return new static('ssh2_auth_password', func_get_args());
    }

    /**
     * Create key-file authentication object.
     */
    public static function viaKeyFile($username, $pubkeyfile, $privkeyfile, $passphrase = null)
    {
        return new static('ssh2_auth_pubkey_file', func_get_args());
    }

    /**
     * Create agent-based authentication object
     */
    public static function viaAgent($username)
    {
        return new static('ssh2_auth_pubkey_file', func_get_args());
    }

    public function authenticateSessionResource($resource)
    {
        if (!Valid::sshSessionResource($resource)) {
            throw new UnexpectedValueException('Parameter must be a valid SSH2 Session resource');
        }

        return call_user_func($this->method, $resource, ...$this->params);
    }
}
