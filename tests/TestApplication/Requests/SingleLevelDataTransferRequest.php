<?php

namespace Tests\TestApplication\Requests;

use Primen\LaravelDataTransferRequests\DataTransferRequest;

class SingleLevelDataTransferRequest extends DataTransferRequest
{
    public int $an_int;
    public string $a_string;
    public float $a_float;
    public array $an_array;
    public bool $a_boolean;
    public object $an_object;
    public mixed $a_mixed;
}
