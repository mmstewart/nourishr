<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => ['required', 'max:255', 'blasp_check'],
            'email' => ['required', 'lowercase', 'email:rfc,dns', 'max:255'],
            'message' => ['required', 'min:10'],
        ];
    }
}
