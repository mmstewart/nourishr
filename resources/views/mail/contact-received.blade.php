<x-mail::message>
# We'd love to hear from you.

Hi {{ $contact->name }},

Thanks for reaching out! We really value your thoughts. If your message requires a follow-up, someone from our team will get back to you within 24 hours.

Here's a copy of what you sent us:

<x-mail::panel>
{{ $contact->message }}
</x-mail::panel>

Thanks,<br>
The {{ config('app.name') }} Team
</x-mail::message>