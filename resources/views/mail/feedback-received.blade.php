<x-mail::message>
# Thanks You for Your Feedback, {{ $feedback->name }}

We've received your message and really appreciate you taking the time to share your thoughts with us. We read every piece of feedback and will be in touch if we have any follow-up questions.

Here's a copy of what you sent us:

<x-mail::panel>
{{ $feedback->message }}
</x-mail::panel>

Thanks,<br>
The {{ config('app.name') }} Team
</x-mail::message>
