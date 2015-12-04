<?php

namespace Moccalotto\Ssh\Contract;

interface AuthenticatorContract
{
    /**
     * Authenticate an ssh2 session.
     *
     * @param resource $resource. The SSH resource
     *
     * @return bool
     *
     * @throws \UnexpectedValueException if the argument provided is not a valid ssh2 session resource.
     */
    public function authenticateSessionResource($resource);
}
