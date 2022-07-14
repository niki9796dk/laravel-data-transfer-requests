<?php

namespace Tests\TestApplication\Requests;

use Primen\LaravelDataTransferRequests\DataTransferRequest;

class RequiredFieldRequest extends DataTransferRequest
{
    public bool $required_bool;
}
