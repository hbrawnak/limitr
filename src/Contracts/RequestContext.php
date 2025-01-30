<?php

namespace Hbrawnak\Limitr\Contracts;

interface RequestContext
{
    public function getIp();
    public function getUserId();
    public function getEndpoint();
}