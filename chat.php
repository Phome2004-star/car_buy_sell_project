<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$conn = db();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$car_id = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;
$receiver_id = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : 0;

// ၁။ အချက်အလက်များ ဆွဲထုတ်ခြင်း (Left Join သုံးထားလို့ ကားမရှိလည်း User name တက်လာမယ်)
$info_stmt = $conn->prepare("
    SELECT u.username as chat_partner_name, c.brand, c.model 
    FROM users u 
    LEFT JOIN car_listings c ON c.id = ? 
    WHERE u.id = ?
");
$info_stmt->execute([$car_id, $receiver_id]);
$info = $info_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($info['chat_partner_name'] ?? 'User'); ?></title>
    <!-- jQuery ပါမှ AJAX က အလုပ်လုပ်မှာပါ -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .chat-container { max-width: 500px; margin: 30px auto; background: #fff; border: 1px solid #ddd; border-radius: 8px; display: flex; flex-direction: column; height: 85vh; font-family: sans-serif; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .chat-header { padding: 12px 15px; background: #000; color: #fff; border-top-left-radius: 8px; border-top-right-radius: 8px; display: flex; align-items: center; }
        .back-btn { text-decoration: none; color: #fff; font-size: 20px; margin-right: 15px; display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 50%; }
        .header-text { flex: 1; }
        .partner-name { font-size: 16px; font-weight: bold; display: block; }
        .car-info { font-size: 11px; color: #ccc; }
        .chat-box { flex: 1; overflow-y: auto; padding: 20px; background: #f9f9f9; display: flex; flex-direction: column; }
        
        /* Message Styling */
        .msg { margin-bottom: 12px; padding: 10px 15px; border-radius: 18px; max-width: 75%; font-size: 14px; line-height: 1.4; word-wrap: break-word; }
        .sent { align-self: flex-end; background: #007bff; color: #fff; border-bottom-right-radius: 2px; }
        .received { align-self: flex-start; background: #e4e6eb; color: #000; border-bottom-left-radius: 2px; }
        
        .chat-input { padding: 15px; border-top: 1px solid #eee; display: flex; background: #fff; }
        .chat-input input { flex: 1; padding: 12px 18px; border: 1px solid #ddd; border-radius: 25px; outline: none; }
        .chat-input button { margin-left: 10px; background: #007bff; color: #fff; border: none; padding: 0 25px; border-radius: 25px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        <a href="javascript:history.back()" class="back-btn">←</a>
        <div class="header-text">
            <span class="partner-name">👤 <?php echo htmlspecialchars($info['chat_partner_name'] ?? 'Unknown User'); ?></span>
            <span class="car-info">
                <?php echo ($car_id > 0) ? "Discussing: " . htmlspecialchars($info['brand'] . ' ' . $info['model']) : "Customer Support"; ?>
            </span>
        </div>
    </div>

    <div class="chat-box" id="chatBox">
        <!-- Messages will be loaded here by AJAX -->
    </div>

    <div class="chat-input">
        <input type="text" id="messageInput" placeholder="Type a message..." autocomplete="off">
        <button id="sendBtn">Send</button>
    </div>
</div>

<script>
    // စာမျက်နှာ အောက်ဆုံးကို အလိုအလျောက် ဆွဲချပေးတဲ့ function
    function scrollToBottom() {
        var chatBox = document.getElementById("chatBox");
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Message များကို ဆွဲထုတ်ခြင်း
    function loadMessages() {
        $.ajax({
            url: 'load_messages_ajax.php',
            type: 'GET',
            data: { 
                car_id: <?php echo $car_id; ?>, 
                receiver_id: <?php echo $receiver_id; ?> 
            },
            success: function(data) {
                $('#chatBox').html(data);
                scrollToBottom();
            }
        });
    }

    // Message ပို့ခြင်း
    function sendMessage() {
        var message = $('#messageInput').val().trim();
        if (message == "") return;

        $.ajax({
            url: 'send_message_ajax.php',
            type: 'POST',
            data: {
                car_id: <?php echo $car_id; ?>,
                receiver_id: <?php echo $receiver_id; ?>,
                message: message,
                is_ajax: 1
            },
            success: function(response) {
                if (response.trim() == "success") {
                    $('#messageInput').val(''); // Input ကို ရှင်းထုတ်မယ်
                    loadMessages(); // message အသစ်ကို ချက်ချင်း ပြန်ဆွဲထုတ်မယ်
                } else {
                    alert("Error: " + response);
                }
            }
        });
    }

    $(document).ready(function() {
        loadMessages(); // စဖွင့်ချင်း load လုပ်မယ်
        setInterval(loadMessages, 3000); // ၃ စက္ကန့်တစ်ခါ update စစ်မယ်

        $('#sendBtn').click(function() {
            sendMessage();
        });

        // Enter ခေါက်ရင်လည်း စာပို့လို့ရအောင် လုပ်မယ်
        $('#messageInput').keypress(function(e) {
            if (e.which == 13) {
                sendMessage();
            }
        });
    });
</script>

</body>
</html>