<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreSetupProfileRequest;

class SetupProfileController extends Controller
{
    public function __invoke(StoreSetupProfileRequest $request)
    {
        $validated = $request->validated();

        auth()->user()->update($validated);

        return to_route('setup');
    }
}
