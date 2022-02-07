<?php

namespace Polly\Support\Authorization;

interface IRoleAuthorizationModel
{
    public function getRole() : ?string;
}
