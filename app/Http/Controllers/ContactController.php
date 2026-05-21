<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Mail\ContactReceived;
use App\Models\Contact;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function __invoke(StoreContactRequest $request)
    {
        if ($request->filled('company')) {
            return to_route('home')->with('success', 'Message received. Thanks for reaching out! We’ve got your details and someone from our team will get back to you within 24 hours.');
        }

        $validated = $request->validated();

        $contact = Contact::create($validated);

        Mail::to(env('VITE_NOURISHR_EMAIL'))->send(new ContactReceived($contact));

        return to_route('home')->with('success', 'Message received. Thanks for reaching out! We’ve got your details and someone from our team will get back to you within 24 hours.');
    }
}
