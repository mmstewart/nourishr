<x-mail::message>
# New Contact Message

**From:** {{ $contact->name }} ({{ $contact->email }})

**Message:**

<x-mail::panel>
{{ $contact->message }}
</x-mail::panel>

</x-mail::message>