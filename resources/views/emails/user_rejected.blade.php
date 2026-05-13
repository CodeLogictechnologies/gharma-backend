<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <h3>Hello {{ $user->name }}</h3>
    <p>Your account has been <b style="color:red;">REJECTED</b>.</p>
    <p>Remarks: {{ $user->remarks }}</p>
</body>

</html>
