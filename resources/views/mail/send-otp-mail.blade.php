@component('mail::message')
Hello {{$user->name}}

Thank you for registering with us.
Please verify your account by entering OTP.
Your OTP is {{$user->email_otp}}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
