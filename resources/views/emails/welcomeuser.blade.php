<!-- <!DOCTYPE html>
<html>
<head>
    <title>Welcome Email</title>
</head>

<body>
<h1>Welcome to the site {{ $users->name }}</h1>
<br/>
<h2>Your registered email-id is {{ $users->email }}</h2>
<h2>Your password is "password"</h2>
<strong>Please do not share this information.</strong>
</body>

</html> -->

@component('mail::message')
    # Welcome
    <p> {{ $users->name }},</p>
    <p>Your registered email is {{ $users->email }}</p>
    <p>Your password is "password"</p>
    <a href="{{ route('adminLogin') }}">Click here for login</a>

    <i>"Please, don't share this information with anyone else."</i>
    <br>
    Thanks,<br>
    Chochangee Samaj Nepal
@endcomponent
