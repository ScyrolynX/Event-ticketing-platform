<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; background: #0f172a; color: #f1f5f9; padding: 2rem;">
    <h2 style="color: #a78bfa;">Your event is coming up!</h2>
    <p>Hi {{ $order->user->name }},</p>
    <p>This is a reminder that <strong>{{ $eventTitle }}</strong> is happening on <strong>{{ $eventDate }}</strong>.</p>
    <p>Your order #{{ $order->id }} is confirmed. See you there!</p>
    <p style="color: #94a3b8; font-size: 0.85rem;">— ScyrolynX Ticketing</p>
</body>
</html>
