<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedbackRequest;
use App\Mail\FeedbackNotification;
use App\Mail\FeedbackReceived;
use App\Models\Feedback;
use Illuminate\Support\Facades\Mail;

class FeedbackController extends Controller
{
    public function __invoke(StoreFeedbackRequest $request)
    {
        if ($request->filled('phone_number')) {
            return to_route('dashboard')->with('success', 'Thanks for reaching out! We really value your thoughts. 
                    If your feedback requires a follow-up, someone from our team will get back to you within 24 hours.');
        }

        $validated = $request->validated();

        $feedback = Feedback::create($validated);

        Mail::to($feedback->email)->send(new FeedbackReceived($feedback));
        Mail::to(env('VITE_NOURISHR_EMAIL'))->send(new FeedbackNotification($feedback));

        return to_route('dashboard')->with('success', 'Thanks for reaching out! We really value your thoughts. 
                    If your feedback requires a follow-up, someone from our team will get back to you within 24 hours.');
    }
}
