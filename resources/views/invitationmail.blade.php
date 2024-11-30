<!DOCTYPE html>
<html>
<head>
    <title>You have been invited to EasySupply</title>
</head>
<body>
    <h1>You've been invited to, {{ $invitationDetails['name'] }} workspace!</h1>
    <p>{{$invitationDetails['message']}}</p>
    <p>Please click on the link below, to access to workspace.</p>
    <p>Invitation link: {{ $invitationDetails['URL'] }}</p>
    <p>Thanks for your attention</p>
</body>
</html>
