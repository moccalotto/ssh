<?php

namespace Moccalotto\Ssh;

use UnexpectedValueException;

class Connect implements Contracts\ConnectorContract
{
    protected $host;

    protected $port;

    public function __construct($host, $port_param = null)
    {
        $port = $port_param === null ? 22 : $port_param;

        if (!Valid::tcpPort($port)) {
            throw new UnexpectedValueException('port must be an integer between 0 and 65535');
        }

        $this->host = $host;
        $this->port = (int) $port;
    }

    public static function to($host, $port = null)
    {
        return new static($host, $port);
    }

    public function port()
    {
        return $this->port;
    }

    public function host()
    {
        return $this->host;
    }

    public function createSessionResource()
    {
        return ssh2_connect($this->host, $this->port);
    }
}
