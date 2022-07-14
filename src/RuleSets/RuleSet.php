<?php

namespace Niki9796dk\LaravelDataTransferRequests\RuleSets;

class RuleSet
{
    /**
     * @var array<string, array>
     */
    private array $ruleSet = [];

    /**
     * Adds a rule to
     *
     * @param string $key
     * @param array|string|RuleSet $rules
     *
     * @return void
     */
    public function add(string $key, array|string|RuleSet $rules): void
    {
        if (is_array($rules)) {
            foreach ($rules as $rule) {
                $this->add($key, $rule);
            }

            return;
        }

        if ($rules instanceof RuleSet) {
            $this->add($key, 'array');
        }

        $this->ruleSet[$key][] = $rules;
    }

    /**
     * Transforms all the added rules into the Laravel validator rules structure
     *
     * @return array
     */
    public function toRules(): array
    {
        $compiledRules = new CompiledRuleSet();

        foreach ($this->ruleSet as $key => $rules) {
            foreach ($this->translateRuleForKey($key, $rules) as $fullKey => $fullKeyRules) {
                $compiledRules->register($fullKey, $fullKeyRules);
            }
        }

        return $compiledRules->toArray();
    }

    /**
     * Translates a single entry within the ruleSet variable, into a set of keys and rules
     *
     * @param string $key
     * @param array $rules
     *
     * @return array
     */
    private function translateRuleForKey(string $key, array $rules): array
    {
        $ruleArray = [];

        foreach ($rules as $rule) {
            if ( ! ($rule instanceof RuleSet)) {
                $ruleArray[$key][] = $rule;

                continue;
            }

            foreach ($rule->toRules() as $subKey => $nestedRule) {
                $nestedKey = $key . '.' . $subKey;

                if ( ! isset($ruleArray[$nestedKey])) {
                    $ruleArray[$nestedKey] = $nestedRule;
                } else {
                    array_push($ruleArray[$nestedKey], ...$nestedRule);
                }
            }
        }

        return $ruleArray;
    }
}
