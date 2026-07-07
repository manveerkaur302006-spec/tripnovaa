<?php
require_once __DIR__ . '/../config/auth.php';
requireRole('customer');
$user = currentUser();
$receiverId = (int)($_GET['user_id'] ?? 0);
$rideId = (int)($_GET['ride_id'] ?? 0);
$messages = [];
if ($receiverId) {
    $stmt = db()->prepare('SELECT * FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY created_at ASC');
    $stmt->execute([$user['id'], $receiverId, $receiverId, $user['id']]);
    $messages = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat - TRIPNOVAA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f5f7fa;max-width:430px;margin:0 auto;min-height:100vh}.page{padding:16px;padding-bottom:120px}.header{display:flex;align-items:center;gap:12px;margin-bottom:18px}.header h1{font-size:20px;color:#1A4980}.message{margin-bottom:12px;padding:12px;border-radius:14px;max-width:80%;display:inline-block}.sent{background:#007BFF;color:#fff;margin-left:auto}.received{background:#fff;color:#333}.small{font-size:12px;color:#666}.input-box{position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid #eee;padding:14px;max-width:430px;margin:0 auto}</style>
</head>
<body>
<div class="page">
<div class="header"><a href="javascript:history.back()" style="color:#333"><i class="fas fa-arrow-left"></i></a><h1>Chat</h1></div>
<?php foreach ($messages as $message): ?>
<div class="message <?= $message['sender_id'] === $user['id'] ? 'sent' : 'received' ?>"><p><?= e($message['message']) ?></p><div class="small"><?= e($message['created_at']) ?></div></div>
<?php endforeach; ?>
</div>
<div class="input-box">
<form method="POST" action="../api/chat_send.php">
<input type="hidden" name="receiver_id" value="<?= $receiverId ?>">
<input type="hidden" name="ride_id" value="<?= $rideId ?>">
<textarea name="message" rows="2" style="width:100%;padding:12px;border:1px solid #eee;border-radius:12px;margin-bottom:10px"></textarea>
<button type="submit" style="width:100%;padding:14px;border:none;border-radius:12px;background:#007BFF;color:#fff;font-weight:600">Send</button>
</form>
</div>
</body>
</html>
