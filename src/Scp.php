<?php

namespace Moccalotto\Ssh;

use Moccalotto\Ssh\Contracts\ConnectorContract;
use Moccalotto\Ssh\Contracts\AuthenticatorContract;

abstract class Scp
{
    /**
     * Send a file via SCP on a one-time SSH conneciton.
     *
     * @param ConnectorContract     $conneciton
     * @param AuthenticatorContract $authentication
     * @param string                $local_file
     * @param string                $remote_file
     * @param int                   $create_mode
     *
     * @return bool;
     */
    public static function sendFile(
        ConnectorContract $connection,
        AuthenticatorContract $authentication,
        $local_file,
        $remote_file,
        $create_mode = 0644
    ) {
        return (new Session($connection, $authentication))->sendFile($local_file, $remote_file, $create_mode);
    }

    /**
     * Fetch a file via SCP on a one-time SSH conneciton.
     *
     * @param ConnectorContract     $conneciton
     * @param AuthenticatorContract $authentication
     * @param string                $remote_file
     * @param string                $local_file
     *
     * @return bool;
     */
    public static function getFile(
        ConnectorContract $connection,
        AuthenticatorContract $authentication,
        $remote_file,
        $local_file
    ) {
        return (new Session($connection, $authentication))->getFile($remote_file, $local_file);
    }
}
