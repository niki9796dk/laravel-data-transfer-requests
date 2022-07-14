<?php

namespace Niki9796dk\LaravelDataTransferRequests\RuleSets;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Contracts\Support\Arrayable;

class CompiledRuleSet implements Arrayable
{
    private array $compiledRules = [];
    private array $nullableKeys = [];

    public function register(string $key, array $rules): void
    {
        if (isset($this->compiledRules[$key])) {
            throw new InvalidArgumentException('Cannot add new rules for existing key: ' . $key);
        }

        $this->compiledRules[$key] = $this->defineRequirebility($key, array_unique($rules));

        if (in_array('nullable', $this->compiledRules[$key], true)) {
            $this->nullableKeys[] = $key;
        }
    }

    private function defineRequirebility(string $key, array $rules): array
    {
        // If it is not required, then nothing needs to change
        if (! in_array('required', $rules, true)) {
            return $rules;
        }

        if ($this->isParentKeyNullable($key)) {
            $this->replaceValueInArray('required', sprintf('required_with:%s', $this->getParentKey($key)), $rules);
        }

        return $rules;
    }

    private function replaceValueInArray($original, $replacement, &$array): void
    {
        $index = array_search($original, $array, true);

        if ($index === false) {
            return;
        }

        $array[$index] = $replacement;
    }

    private function getParentKey(string $key): ?string
    {
        $key = Str::of($key);

        if (! $key->contains('.')) {
            return null;
        }

        return $key->beforeLast('.');
    }

    private function isParentKeyNullable(string $key): bool
    {
        return in_array($this->getParentKey($key), $this->nullableKeys, true);
    }

    public function toArray(): array
    {
        return $this->compiledRules;
    }
}
