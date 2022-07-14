<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;
use Illuminate\Validation\ValidationException;
use Niki9796dk\LaravelDataTransferRequests\Attributes\Rules;
use Niki9796dk\LaravelDataTransferRequests\DataTransferRequest;

class DataTransferRequestTest extends FeatureTestCase
{
    /** @test */
    public function it_can_throws_validation_exception_on_missing_fields(): void
    {
        // Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The required int field is required');

        // Act
        new BasicDataTransferRequest([/* No Data */]);
    }

    /** @test */
    public function it_can_throws_validation_exception_on_wrong_field_type(): void
    {
        // Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The required int must be an integer');

        // Act
        new BasicDataTransferRequest(['requiredInt' => 'notAnInt']);
    }

    /** @test */
    public function it_does_not_throw_exceptions_for_correct_data_type(): void
    {
        // Act
        $instance = new BasicDataTransferRequest(['requiredInt' => 123]);

        // Assert
        $this->assertEquals($instance->requiredInt, 123);
    }

    /** @test */
    public function it_can_handle_nested_data_transfer_request_objects(): void
    {
        // Act
        $instance = new class ([
            'requiredNested' => ['requiredInt' => 123]
        ]) extends DataTransferRequest {
            public BasicDataTransferRequest $requiredNested;
        };

        // Assert
        $this->assertEquals($instance->requiredNested->requiredInt, 123);
    }

    /** @test */
    public function it_can_handle_optional_nested_data_transfer_request_objects_with_null_values(): void
    {
        // Act
        $instance = new class (['requiredBool' => true]) extends DataTransferRequest {
            public bool $requiredBool;
            public ?BasicDataTransferRequest $optionalNested;
        };

        // Assert
        $this->assertTrue($instance->requiredBool);
        $this->assertNull($instance->optionalNested?->requiredInt);
    }

    /** @test */
    public function it_can_handle_optional_nested_data_transfer_request_objects_with_actual_values(): void
    {
        // Act
        $instance = new class (['optionalNested' => ['requiredInt' => 123]]) extends DataTransferRequest {
            public ?BasicDataTransferRequest $optionalNested;
        };

        // Assert
        $this->assertEquals(123, $instance->optionalNested?->requiredInt);
        $this->assertEquals(null, $instance->optionalNested?->optionalString);
    }

    /** @test */
    public function it_can_handle_required_fields_within_optional_keys(): void
    {
        // Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The optional nested.required bool field is required when optional nested is present');

        // Act
        new class (['optionalNested' => ['requiredInt' => 123, /* Is missing requiredBool */]]) extends DataTransferRequest {
            public ?MultiKeyDataTransferRequest $optionalNested;
        };
    }

    /** @test */
    public function it_understands_rules_attribute(): void
    {
        // Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The mixed must be an integer');

        // Act
        new class (['mixed' => 'blabla']) extends DataTransferRequest
        {
            #[Rules('integer', 'max:10')]
            public mixed $mixed;
        };
    }

    /** @test */
    public function it_understands_rules_attribute_max(): void
    {
        // Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The mixed must not be greater than 10');

        // Act
        new class (['mixed' => 11]) extends DataTransferRequest
        {
            #[Rules('integer', 'max:10')]
            public mixed $mixed;
        };
    }
}

class BasicDataTransferRequest extends DataTransferRequest {
    public int $requiredInt;
    public ?string $optionalString;
}

class MultiKeyDataTransferRequest extends DataTransferRequest {
    public int $requiredInt;
    public bool $requiredBool;
}
