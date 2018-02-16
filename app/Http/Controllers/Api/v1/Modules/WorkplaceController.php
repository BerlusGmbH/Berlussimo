<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Services\PhoneLocator;
use Illuminate\Http\Request;

class WorkplaceController extends Controller
{
    public function show(Request $request, PhoneLocator $phoneLocator)
    {
        return response()->json([
            'has_phone' => $phoneLocator->workplaceHasPhone()
        ]);
    }
}