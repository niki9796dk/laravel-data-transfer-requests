<?php

namespace Primen\LaravelDataTransferRequests\Attributes;

use Attribute;
use Primen\LaravelDataTransferRequests\RuleSets\RuleSet;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Rules implements AddsRules
{
    private array $rules;

    public function __construct(...$rules)
    {
        $this->rules = $rules;
    }

    /**
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
