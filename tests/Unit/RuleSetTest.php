<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Primen\LaravelDataTransferRequests\RuleSets\RuleSet;

class RuleSetTest extends TestCase
{
    /** @test */
    public function it_can_add_single_level_keys(): void
    {
        // Arrange
        $ruleSet = new RuleSet();

        // Act
        $ruleSet->add('key1', 'integer');
        $ruleSet->add('key2', ['bool', 'integer']);

        // Assert
        $this->assertEquals([
            'key1' => ['integer'],
            'key2' => ['bool', 'integer'],
        ], $ruleSet->toRules());
    }
}
