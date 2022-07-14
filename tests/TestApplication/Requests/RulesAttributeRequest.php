<?php

namespace Tests\TestApplication\Requests;

use Niki9796dk\LaravelDataTransferRequests\Attributes\Rules;
use Niki9796dk\LaravelDataTransferRequests\DataTransferRequest;

class RulesAttributeRequest extends DataTransferRequest
{
    #[Rules('integer', 'max:10')]
    public mixed $nested;
}
