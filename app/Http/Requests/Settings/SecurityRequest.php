<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Laravel\Fortify\Features;
use Laravel\Fortify\InteractsWithTwoFactorState;

class SecurityRequest extends FormRequest
{
    use InteractsWithTwoFactorState;

    public function authorize()
    {
        return Features::enabled(Features::twoFactorAuthentication());
    }

    public function rules()
    {
        return [];
    }
}
