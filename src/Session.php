<?php

namespace Moccalotto\Ssh;

use UnexpectedValueException;
use Moccalotto\Ssh\Contracts\ConnectorContract;
use Moccalotto\Ssh\Contracts\AuthenticatorContract;
use Moccalotto\Ssh\Exceptions\ConnectionException;
use Moccalotto\Ssh\Exceptions\AuthenticationException;

class Session
{
    /**
     * SSH2 Session Resource.
     *
     * @var resource
     */
    protected $session;

    /**
     * The terminal to use for shells and one-off-executions.
     *
     * @var Terminal
     */
    protected $terminal;

    /**
     * Construct new SSH2 Session.
     *
     * @param ConnectorContract     $connection
     * @param AuthenticatorContract $authentication
     *
     * @throws ConnectionException     if connection could not be established.
     * @throws AuthenticationException if the user could not be authenticated.
     */
    public function __construct(ConnectorContract $connection, AuthenticatorContract $authentication)
    {
        $session = $connection->createSessionResource();

        if (!$session) {
            throw new ConnectionException('Could not establish SSH connection');
        }

        if (!$authentication->authenticateSessionResource($session)) {
            throw new AuthenticationException('Could not authenticate user');
        }
        $this->session = $session;

        $this->terminal = Terminal::create();
    }

    /**
     * Get the SSH2 fingerprint algorithm ID.
     *
     * @param string $algorithm md5|hex
     *
     * @return int
     *
     * @throws UnexpectedValueException if the encoding name is incorrect
     */
    public function getFingerprintAlgorithmId($algorithm)
    {
        $map = [
            'md5' => SSH2_FINGERPRINT_MD5,
            'sha1' => SSH2_FINGERPRINT_SHA1,
        ];

        if (!isset($map[$algorithm])) {
            throw new UnexpectedValueException(sprintf(
                'Incorrect algorithm "%s". You must use one of [%s]',
                $algorithm,
                implode(', ', array_keys($map))
            ));
        }

        return $map[$algorithm];
    }

    /**
     * Get the SSH2 fingerprint encoding ID.
     *
     * @param string $encoding hex|raw
     *
     * @return int
     *
     * @throws UnexpectedValueException if the encoding name is incorrect
     */
    public function getFingerprintEncodingId($encoding)
    {
        $map = [
            'hex' => SSH2_FINGERPRINT_HEX,
            'raw' => SSH2_FINGERPRINT_RAW,
        ];

        if (!isset($map[$encoding])) {
            throw new UnexpectedValueException(sprintf(
                'Incorrect encoding "%s". You must use one of [%s]',
                $encoding,
                implode(', ', array_keys($map))
            ));
        }

        return $map[$encoding];
    }

    /**
     * Get the servers hostkey/fingerprint.
     *
     * @param string $algorithm sha1|md5
     * @param string $encoding  hex|raw
     *
     * @return string
     *
     * @throws UnexpectedValueException if $algorithm or $encoding is incorrect
     */
    public function fingerprint($algorithm = 'sha1', $encoding = 'hex')
    {
        return ssh2_fingerprint($this->getFingerprintAlgorithmId($algorithm) | $this->getFingerprintEncodingId($encoding));
    }

    /**
     * Set the terminal to use for the next execution or shell.
     *
     * @param Terminal $terminal
     *
     * @return $this
     */
    public function withTerminal(Terminal $terminal)
    {
        $this->terminal = $terminal;

        return $this;
    }

    /**
     * Execute a command.
     *
     * A program is executed with the current terminal settings.
     * Its output is returned when the program has executed.
     * It is therefore IMPORTANT you only execute programs that terminate.
     *
     * @param string $cmd the command to execute
     *
     * @return string the output of the commmand
     */
    public function execute($cmd)
    {
        $stream = new ExecutionStream(ssh2_exec(
            $this->session,                 // the session resource.
            $cmd,                           // the command to be executed.
            null,                           // the PTY to use. Hardcoded in this code.
            null,                           // the environment variables to pass. This does NOT seem to work. So we simply ignore them for now.
            $this->terminal->getWidth(),    // the width of the terminal
            $this->terminal->getHeight(),   // the height of the terminal
            $this->terminal->getDimensionUnits() // the units (pixels or chars) of the width and height
        ));

        return $stream->readAndClose(true);
    }

    /**
     * Execute a command and manage its input/output.
     *
     * A program is executed with the current temrinal settings.
     * The IO stream is passed to callback that then is responsible
     * for talking to the program through the terminal.
     *
     * The iostream is closed when callback returns.
     *
     * signature of callback:
     * mixed callback(ExecutionStream $io)
     *
     * @param callable $callback
     *
     * @return mixed the value that $calback returned.
     */
    public function run(callback $callback)
    {
        $stream = new ExecutionStream(ssh2_exec(
            $this->session,                 // the session resource.
            $cmd,                           // the command to be executed.
            null,                           // the PTY to use. Hardcoded in this code.
            null,                           // the environment variables to pass. This does NOT seem to work. So we simply ignore them for now.
            $this->terminal->getWidth(),    // the width of the terminal
            $this->terminal->getHeight(),   // the height of the terminal
            $this->terminal->getDimensionUnits() // the units (pixels or chars) of the width and height
        ));

        $result = $callback($stream);
        $stream->close();

        return $result;
    }

    /**
     * Start a shell with the currenet terminal settings.
     *
     * sicnature of callback:
     * mixed callback(ExecutionStream $io)
     *
     * @param callable $callback
     *
     * @return mixed the result from executing callback.
     */
    public function shell(callable $callback)
    {
        $stream = new ExecutionStream(ssh2_shell(
            $this->session,                 // the session resource.
            'vanilla',                      // the PTY to use. Hardcoded for now.
            null,                           // the environment variables to pass. This does NOT seem to work. So we simply ignore them for now.
            $this->terminal->getWidth(),    // the width of the terminal
            $this->terminal->getHeight(),   // the height of the terminal
            $this->terminal->getDimensionUnits() // the units (pixels or chars) of the width and height
        ));

        $result = $callback($stream->async());
        $stream->close();

        return $result;
    }

    /**
     * Send a file to remote server via SCP.
     *
     * @param string $local_file
     * @param string $remote_file
     * @param int    $create_mode The remote file will be created with the specified mode.
     *
     * @return bool true if successful.
     */
    public function sendFile($local_file, $remote_file, $create_mode = 0644)
    {
        return ssh2_scp_send($this->session, $local_file, $remote_file, $create_mode);
    }

    /**
     * Fetch a file from the remote server via SCP.
     *
     * @param string $remote_file
     * @param string $local_file
     *
     * @return bool true if successful
     */
    public function getFile($remote_file, $local_file)
    {
        return ssh2_scp_recv($this->session, $remote_file, $local_file);
    }

    public function sftp()
    {
        return new Sftp(ssh2_sftp($this->session));
    }
}
