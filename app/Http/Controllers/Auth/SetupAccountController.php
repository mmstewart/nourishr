<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreSetupAccountRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SetupAccountController extends Controller
{
    public function __invoke(StoreSetupAccountRequest $request)
    {
        $validated = $request->validated();

        $user = User::create($validated);

        Auth::login($user);

        return to_route('setup');
    }
}
