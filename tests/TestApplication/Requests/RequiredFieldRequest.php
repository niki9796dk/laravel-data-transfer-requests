<?php

namespace Tests\TestApplication\Requests;

use Niki9796dk\LaravelDataTransferRequests\DataTransferRequest;

class RequiredFieldRequest extends DataTransferRequest
{
    public bool $required_bool;
}
