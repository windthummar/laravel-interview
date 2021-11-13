@component('mail::message')
# Introduction

Hey,

How are you, we are {{ config('app.name') }}, You can register with us on this link.

@component('mail::button', ['url' => url('/')] )
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
