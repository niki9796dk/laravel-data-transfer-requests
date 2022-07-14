<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;

class ControllerEndpointTest extends FeatureTestCase
{
    /** @test */
    public function it_can_assign_single_level_data(): void
    {
        // Arrange
        $this->assertTrue(true);

        // Act
        $response = $this->postJson('/single-level-data', [
            'an_int'    => 123,
            'a_string'  => 'monkey!',
            'a_float'   => 4.20,
            'an_array'  => [1, 2, 3],
            'a_boolean' => true,
            'an_object' => ['abc' => 123],
            'a_mixed'   => null,
        ]);

        // Assert
        $response->assertOk();
        $response->assertJson([
            'int'    => 123,
            'string' => 'monkey!',
            'float'  => 4.20,
            'array'  => [1, 2, 3],
            'bool'   => true,
            'object' => ['abc' => 123],
            'mixed'  => null,
        ]);
    }

    /** @test */
    public function it_returns_422_on_invalid_data_types(): void
    {
        // Arrange
        $this->assertTrue(true);

        // Act
        $response = $this->postJson('/single-level-data', [
            'an_int'   => 123,
            'a_string' => 'monkey!',
            'a_float'  => 'monkey!',
            'an_array' => [1, 2, 3],
        ]);

        // Assert
        $response->assertUnprocessable();
    }

    /** @test */
    public function it_returns_422_on_missing_fields(): void
    {
        // Arrange
        $this->assertTrue(true);

        // Act
        $response = $this->postJson('/required-field', [
            // Empty body
        ]);

        // Assert
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'required_bool' => 'The required bool field is required.',
        ]);
    }

    /** @test */
    public function it_can_handle_required_fields(): void
    {
        // Arrange
        $this->assertTrue(true);

        // Act
        $response = $this->postJson('/required-field', [
            'required_bool' => false,
        ]);

        // Assert
        $response->assertOk();
        $response->assertJson([
            'required_bool' => false,
        ]);
    }

    /** @test */
    public function it_can_handle_nested_fields(): void
    {
        // Arrange
        $this->assertTrue(true);

        // Act
        $response = $this->postJson('/nested-field', [
            'nested' => ['required_bool' => true],
        ]);

        // Assert
        $response->assertOk();
        $response->assertJson([
            'nested' => true,
        ]);
    }
}
