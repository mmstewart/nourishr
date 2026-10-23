<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Mail\ContactNotification;
use App\Mail\ContactReceived;
use App\Models\Contact;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function __invoke(StoreContactRequest $request)
    {
        $message = 'Message received. Thanks for reaching out! We’ve got your details and someone from our team will get back to you within 24 hours.';

        if ($request->filled('company')) {
            return to_route('home')->with('success', $message);
        }

        $validated = $request->validated();

        $contact = Contact::create($validated);

        Mail::to($contact->email)->send(new ContactReceived($contact));
        Mail::to(config('mail.contact_address'))->send(new ContactNotification($contact));

        return $request->user()
            ? to_route('dashboard')->with('success', $message)
            : to_route('home')->with('success', $message);
    }
}
