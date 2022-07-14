<?php

namespace Tests\TestApplication\Requests;

use Primen\LaravelDataTransferRequests\Attributes\Rules;
use Primen\LaravelDataTransferRequests\DataTransferRequest;

class RulesAttributeRequest extends DataTransferRequest
{
    #[Rules('integer', 'max:10')]
    public mixed $nested;
}
