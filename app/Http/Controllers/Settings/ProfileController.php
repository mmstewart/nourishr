<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateProfileRequest;
use App\Support\CountryList;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $countries = CountryList::all();

        $mustVerifyEmail = $request->user() instanceof MustVerifyEmail;

        $status = $request->session()->get('status');

        return Inertia::render('settings/Profile', [
            'countries' => $countries,
            'mustVerifyEmail' => $mustVerifyEmail,
            'status' => $status,
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return back();
    }
}
