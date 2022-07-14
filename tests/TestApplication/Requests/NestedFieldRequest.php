<?php

namespace Tests\TestApplication\Requests;

use Primen\LaravelDataTransferRequests\DataTransferRequest;

class NestedFieldRequest extends DataTransferRequest
{
    public RequiredFieldRequest $nested;
}
