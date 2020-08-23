
@component('mail::message')
# hello {{$user->name}}

You have changed your mail. Please verify your email using this button:

@component('mail::button', ['url' => route('verify',$user->verification_token)])
Verify Account
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent