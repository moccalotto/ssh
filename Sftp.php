<?php 

namespace Moccalotto\Ssh;

use UnexpectedValueException;
use Moccalotto\Ssh\Contract\ConnectorContract;
use Moccalotto\Ssh\Contract\AuthenticatorContract;

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

    public function wrapperForFile($filename)
    {
        return sprintf(
            'ssh2.sftp://%s%s/%s', 
            $this->resource, 
            ssh2_sftp_realpath($this->resource, '.'), 
            $filename
        );
    }

    /**
     * Get contents of remote file
     *
     * @param string $filename
     * @return string The contents of the file
     */
    public function getContents($filename)
    {
        return file_get_contents($this->wrapperForFile($filename));
    }

    /**
     * Write a string to a remote file
     *
     * @param string $filename
     * @param string $contents
     * @return int The number of bytes written
     */
    public function putContents($filename, $contents)
    {
        return file_put_contents($this->wrapperForFile($filename), $contents);
    }

    /**
     * Remote fopen
     *
     * @param string $filename
     * @param string $mode
     * @return resource
     */
    public function fopen($filename, $mode)
    {
        return fopen($this->wrapperForFile($filename), $mode);
    }

    /**
     * Remote chmod
     *
     * @param string $filename
     * @param int $mode
     * @return bool success
     */
    public function chmod($filename, $mode)
    {
        return ssh2_sftp_chmod($this->resource, $filename, $mode);
    }

    /**
     * Stat a symbolic link
     *
     * @param string $path
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
     * @param int $mode
     * @param bool $recursive
     * @return bool
     */
    public function mkdir($dirname, $mode, $recursive = false)
    {
        return ssh2_sftp_mkdir($this->resource, $dirname, $mode, $recursive);
    }

    /**
     * Return the target of a remote link
     *
     * @param string $link
     * @return string
     */
    public function readlink($link)
    {
        ssh2_sftp_readlink($this->resource, $link);
    }

    /**
     * Remote realpath
     *
     * @param string $filename
     * @return string
     */
    public function realpath($filename)
    {
        return ssh2_sftp_realpath($this->resource, $path);
    }

    /**
     * Remote rename
     *
     * @param string $from
     * @param string $to
     * @return bool
     */
    public function rename($from, $to)
    {
        ssh2_sftp_rename($this->resource, $from, $to);
    }

    /**
     * Remote rmdir
     *
     * @param string $dirname
     * @return bool
     */
    public function rmdir($dirname)
    {
        ssh2_sftp_rmdir($this->resource, $dirname);
    }

    /**
     * Remote stat
     *
     * @param string $path
     * @return array
     */
    public function stat($path)
    {
        ssh2_sftp_stat($this->resource, $path);
    }

    /**
     * Remote symlink
     *
     * @param string $target
     * @param string $link
     * @return bool
     */
    public function symlink($target, $link)
    {
        ssh2_sftp_symlink($this->resource, $target, $link);
    }

    /**
     * Remote unlink / delete
     *
     * @param string $filename
     * @return bool
     */
    public function unlink($filename)
    {
        ssh2_sftp_unlink($this->resource, $filename);
    }
}
