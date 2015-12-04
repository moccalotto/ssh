<?php

namespace Moccalotto\Ssh\Contract;

interface ConnectorContract
{
    /**
     * Get the port that is to be connected to.
     *
     * @return int;
     */
    public function port();

    /**
     * Get the host (name or IP) that is to be connected to.
     *
     * @return string
     */
    public function host();

    /**
     * Create SSH2 Session.
     *
     * @return resource
     */
    public function createSessionResource();
}
