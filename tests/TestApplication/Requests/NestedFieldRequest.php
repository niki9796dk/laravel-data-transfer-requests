<?php

namespace Tests\TestApplication\Requests;

use Niki9796dk\LaravelDataTransferRequests\DataTransferRequest;

class NestedFieldRequest extends DataTransferRequest
{
    public RequiredFieldRequest $nested;
}
