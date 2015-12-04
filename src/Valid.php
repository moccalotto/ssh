<?php

namespace Moccalotto\Ssh;

class Valid
{
    /**
     * Check if param is a valid TCP port number.
     *
     * @param mixed $port The valaue to be checked.
     *
     * @return bool
     */
    public static function tcpPort($port)
    {
        if (false === filter_var($port, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 65535]])) {
            return false;
        }

        return true;
    }

    /**
     * Check if param is a valid stream resource.
     *
     * @param resource $resource The value to be checked.
     *
     * @return bool
     */
    public static function streamResource($resource)
    {
        if (!is_resource($resource)) {
            return false;
        }

        if (get_resource_type($resource) !== 'stream') {
            return false;
        }

        $info = stream_get_meta_data($resource);
        if ($info['stream_type'] !== 'SSH2 Channel') {
            return false;
        }

        return true;
    }

    /**
     * Check if param is a valid SSH2 Session resource.
     *
     * @param mixed $resource The value to be checked.
     *
     * @return bool
     */
    public static function sshSessionResource($resource)
    {
        if (!is_resource($resource)) {
            return false;
        }

        if (get_resource_type($resource) !== 'SSH2 Session') {
            return false;
        }

        return true;
    }

    /**
     * Check if param is a valid SSH2 SFTP Resource.
     *
     * @param mixed $resource THe value to be checked.
     *
     * @return bool
     */
    public static function sshSftpResource($resource)
    {
        if (!is_resource($resource)) {
            return false;
        }

        if (get_resource_type($resource) !== 'SSH2 SFTP') {
            return false;
        }

        return true;
    }
}
