<?php

namespace Moccalotto\Ssh;

use UnexpectedValueException;

class Sftp
{
    protected $resource;

    public function __construct($resource)
    {
        if (!Valid::sshSftpResource($resource)) {
            throw new UnexpectedValueException('Parameter must be a valid stream resource');
        }
        $this->resource = $resource;
    }

    public function streamUri($remote_file)
    {
        return sprintf(
            'ssh2.sftp://%s%s/%s',
            $this->resource,
            ssh2_sftp_realpath($this->resource, '.'),
            $remote_file
        );
    }

    /**
     * Get contents of remote file.
     *
     * @param string $remote_file
     *
     * @return string The contents of the file
     */
    public function getContents($remote_file)
    {
        return file_get_contents($this->streamUri($remote_file));
    }

    /**
     * Write a string to a remote file.
     *
     * @param string $remote_file
     * @param string $contents
     *
     * @return int The number of bytes written
     */
    public function putContents($remote_file, $contents)
    {
        return file_put_contents($this->streamUri($remote_file), $contents);
    }

    /**
     * Remote fopen.
     *
     * @param string $remote_file
     * @param string $mode
     *
     * @return resource
     */
    public function fopen($remote_file, $mode)
    {
        return fopen($this->streamUri($remote_file), $mode);
    }

    /**
     * Remote chmod.
     *
     * @param string $remote_file
     * @param int    $mode
     *
     * @return bool success
     */
    public function chmod($remote_file, $mode)
    {
        return ssh2_sftp_chmod($this->resource, $remote_file, $mode);
    }

    /**
     * Stat a symbolic link.
     *
     * @param string $path
     *
     * @return array
     */
    public function lstat($path)
    {
        return ssh2_sftp_lstat($this->resource, $path);
    }

    /**
     * Remote mkdir.
     *
     * @param string $dirname
     * @param int    $mode
     * @param bool   $recursive
     *
     * @return bool
     */
    public function mkdir($dirname, $mode, $recursive = false)
    {
        return ssh2_sftp_mkdir($this->resource, $dirname, $mode, $recursive);
    }

    /**
     * Return the target of a remote link.
     *
     * @param string $link
     *
     * @return string
     */
    public function readlink($link)
    {
        ssh2_sftp_readlink($this->resource, $link);
    }

    /**
     * Remote realpath.
     *
     * @param string $remote_file
     *
     * @return string
     */
    public function realpath($remote_file)
    {
        return ssh2_sftp_realpath($this->resource, $path);
    }

    /**
     * Remote rename.
     *
     * @param string $from
     * @param string $to
     *
     * @return bool
     */
    public function rename($from, $to)
    {
        ssh2_sftp_rename($this->resource, $from, $to);
    }

    /**
     * Remote rmdir.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function rmdir($dirname)
    {
        ssh2_sftp_rmdir($this->resource, $dirname);
    }

    /**
     * Remote stat.
     *
     * @param string $path
     *
     * @return array
     */
    public function stat($path)
    {
        ssh2_sftp_stat($this->resource, $path);
    }

    /**
     * Remote symlink.
     *
     * @param string $target
     * @param string $link
     *
     * @return bool
     */
    public function symlink($target, $link)
    {
        ssh2_sftp_symlink($this->resource, $target, $link);
    }

    /**
     * Remote unlink / delete.
     *
     * @param string $remote_file
     *
     * @return bool
     */
    public function unlink($remote_file)
    {
        ssh2_sftp_unlink($this->resource, $remote_file);
    }
}
