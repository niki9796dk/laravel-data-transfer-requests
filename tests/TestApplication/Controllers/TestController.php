<?php

namespace Tests\TestApplication\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Tests\TestApplication\Requests\NestedFieldRequest;
use Tests\TestApplication\Requests\RequiredFieldRequest;
use Tests\TestApplication\Requests\SingleLevelDataTransferRequest;

class TestController extends Controller
{
    public function singleLevelData(SingleLevelDataTransferRequest $request): JsonResponse
    {
        return response()->json([
            'int'    => $request->an_int,
            'float'  => $request->a_float,
            'string' => $request->a_string,
            'array'  => $request->an_array,
            'bool'   => $request->a_boolean,
            'object' => $request->an_object,
            'mixed'  => $request->a_mixed,
        ]);
    }

    public function requiredField(RequiredFieldRequest $request): JsonResponse
    {
        return response()->json([
            'required_bool' => $request->required_bool,
        ]);
    }

    public function nestedField(NestedFieldRequest $request): JsonResponse
    {
        return response()->json([
            'nested' => $request->nested->required_bool,
        ]);
    }
}
