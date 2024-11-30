<!DOCTYPE html>
<html>
<head>
    <title>Order recived</title>
</head>
<body>
    <h1>You've recived an order from {{ $orderDetails['name'] }}!</h1>
    <p>Please click on the link below, and confirm that you have received the order and inform your customer about it.</p>
    <p>Order link: {{ $orderDetails['orderUrl'] }}</p>
    <p>Thanks for your attention</p>
</body>
</html>
