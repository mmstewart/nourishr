<x-mail::message>
# New Feedback Received

**From:** {{ $feedback->name }} ({{ $feedback->email }})

**Message:**

<x-mail::panel>
{{ $feedback->message }}
</x-mail::panel>

</x-mail::message>