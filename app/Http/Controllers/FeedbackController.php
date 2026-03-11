<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Http\Requests\StoreFeedbackRequest;
use Illuminate\Support\Facades\Mail;

class FeedbackController extends Controller
{
    public function store(StoreFeedbackRequest $request)
    {
        if ($request->filled('phone_number')) {
            return to_route('dashboard')->with('success', 'Thanks for reaching out! We really value your thoughts. 
                    If your feedback requires a follow-up, someone from our team will get back to you within 24 hours.');
        }

        $validated = $request->validated();

        Feedback::create($validated);

        // todo
        // Mail::to('admin@yourwebsite.com')->send(new FeedbackReceived($validated));

        return to_route('dashboard')->with('success', 'Thanks for reaching out! We really value your thoughts. 
                    If your feedback requires a follow-up, someone from our team will get back to you within 24 hours.');
    }
}
