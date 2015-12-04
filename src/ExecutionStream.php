<?php

namespace Moccalotto\Ssh;

use UnexpectedValueException;

class ExecutionStream
{
    /**
     * stream that represents stdin and stdout in the current execution channel.
     *
     * @var resource
     */
    protected $stdio;

    /**
     * stream that represents stderr in the current execution channel.
     *
     * @var resource
     */
    protected $stderr;

    /**
     * Construct an execution stream in blocking mode.
     *
     * @param resource $stream
     *
     * @throws UnexpectedValueException if $stream is not a valid stream
     */
    public function __construct($stream)
    {
        if (!Valid::streamResource($stream)) {
            throw new UnexpectedValueException('Parameter must be a valid stream resource');
        }
        $stderr = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
        if (!$stderr) {
            throw new UnexpectedValueException('Parameter is a valid stream, but does not contain an »stderr« substream');
        }

        $this->stdio = $stream;
        $this->stderr = $stderr;
    }

    /**
     * Set the stream into async (non-blocking) moode.
     *
     * Read operations will not wait for EOF or EOL, 
     * they will return the unconsumed data from the stream
     * if available, and return "" if no data is available.
     *
     * When stream is in async mode, you should use the wait() method
     * to wait for data to become available in the stream.
     *
     * @return $this
     */
    public function async()
    {
        stream_set_blocking($this->stdio, 0);
        stream_set_blocking($this->stderr, 0);

        return $this;
    }

    /**
     * Set the stream into synchronous (blocking) mode.
     *
     * Read operations will block until the necessary data becomes available.
     * This can cause infinite waits if the data never becomes available.
     *
     * @return $this
     */
    public function sync()
    {
        stream_set_blocking($this->stdio, 1);
        stream_set_blocking($this->stderr, 1);

        return $this;
    }

    /**
     * Sleep for a number of seconds.
     *
     * @param float $seconds
     *
     * @return $this
     */
    public function wait($seconds)
    {
        $microseconds_per_second = 1000000;
        usleep(round($microseconds_per_second * $seconds));

        return $this;
    }

    /**
     * Write $string to stdio.
     *
     * @return $this;
     */
    public function write($string)
    {
        fwrite($this->stdio, $string);

        return $this;
    }

    /**
     *  Write $string to stdio and append a newline character.
     *
     *  @return $this;
     */
    public function writeline($string)
    {
        return $this->write($string.PHP_EOL);
    }

    /**
     * Read $length bytes from stdio.
     *
     * If the stream is in sync mode, this method will wait until
     * $length bytes of data becomes available.
     * If the stream is in async mode, this method may return less
     * than $length bytes, if less that $length bytes are available.
     *
     * @see fgets
     *
     * @param int $length
     *
     * @return string
     */
    public function read($length)
    {
        return fread($this->stdio, $length);
    }

    /**
     * Read line from stdio.
     *
     * If the stream is in sync mode, this method will wait until
     * a newline char becomes available.
     * If the stream is in async mode, and no newline char is available,
     * this method simply returns the contents of the stream.
     *
     * @see fgets
     *
     * NB. the newline character is returned.
     *
     * @return string
     */
    public function readline()
    {
        return fgets($this->stdio);
    }

    /**
     * Read stdio to the end of stream.
     *
     * If the stream is in sync mode, this method will wait until
     * the stream closes before it returns the contents of the stream.
     * If the stream is in sync mode, the current contents of the stream
     * will be returned.
     *
     * @see stream_get_contents
     *
     * @return string
     */
    public function readToEnd()
    {
        return stream_get_contents($this->stdio);
    }

    /**
     * Read a $length bytes from stderr.
     *
     * @see ExecutionStream::read
     *
     * @param int $length
     *
     * @return string
     */
    public function readError($length)
    {
        return fread($this->stderr, $length);
    }

    /**
     * Read line from stderr.
     *
     * @see ExecutionStream::readline
     *
     * @return string
     */
    public function readErrorLine()
    {
        return fgets($this->stderr);
    }

    /**
     * Read stderr to the end of stream.
     *
     * @see ExecutionStream::readToEnd
     *
     * @return string
     */
    public function readErrorToEnd()
    {
        return stream_get_contents($this->stderr);
    }

    /**
     * Close the stdio stream (and by extension stderr).
     *
     * @return $this
     */
    public function close()
    {
        fclose($this->stdio);

        return $this;
    }

    /**
     * Read all unconsumed data from the stdio stream and close it.
     *
     * @param bool $wait_for_eof should we read until EOF or until end of input.
     *
     * @return string
     */
    public function readAndClose($wait_for_eof)
    {
        if ($wait_for_eof) {
            $this->sync();
        } else {
            $this->async();
        }
        $result = $this->readToEnd();
        $this->close();

        return $result;
    }
}
