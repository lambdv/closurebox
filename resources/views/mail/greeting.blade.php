<x-mail::message>
# Welcome to {{$username}}

build something awesome.

<x-mail::button :url="''">
Button Text
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
