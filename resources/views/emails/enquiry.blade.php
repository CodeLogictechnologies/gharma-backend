<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Chochangee Samaj Nepal!</title>
    <style>
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            border-radius: 10px;
        }

        .email-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .email-text {
            font-size: 16px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <h1 class="email-title">Enquiry!</h1>
        @if(!empty($enquiry))
        <h3>User: {{ $enquiry['name'] }} </h3>
        <h3>Number: {{$enquiry['phone']}} </h3>
        <h3>Number: {{$enquiry['subject']}} </h3>
        <h3> Email: {{$enquiry['email']}} </h3>
        <h3> Message: {{$enquiry['message']}}</h3>
        @else
        No Email
        @endif

        <p class="email-text">Thank you for joining us!</p>

        <p class="email-text">Best regards,<br>

            The Chochangee Samaj Nepal Team</p>
    </div>
</body>

</html>