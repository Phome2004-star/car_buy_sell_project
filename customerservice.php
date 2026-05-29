<?php
require_once 'db.php';
$conn = db();
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// --- Customer Service အတွက် Admin ID 8 ကိုပဲ သုံးမယ် ---
$admin_id = 8; 
$car_id = 0; // Support အတွက်မို့ 0 ထားတယ်
$user_id = $_SESSION['user_id'];

// Admin နာမည်ကို ယူမယ်
$seller_name = "Phome Htet Support";
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$admin_id]);
$res = $stmt->fetch();
if ($res) $seller_name = $res['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat with Seller | Phome Htet</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Chat UI Style */
        body { background: #f0f2f5; font-family: 'Inter', sans-serif; }
        .chat-container { max-width: 800px; margin: 20px auto; background: #fff; height: 80vh; display: flex; flex-direction: column; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .chat-header { padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 10px; font-weight: bold; }
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; background: #ffffff; display: flex; flex-direction: column; gap: 10px; }
        
        /* Message Bubbles */
        .msg { max-width: 70%; padding: 10px 15px; border-radius: 18px; font-size: 14px; line-height: 1.4; position: relative; }
        .msg.user { align-self: flex-end; background: #000; color: #fff; border-bottom-right-radius: 4px; }
        .msg.support { align-self: flex-start; background: #e4e6eb; color: #000; border-bottom-left-radius: 4px; }
        
        .chat-input-area { padding: 15px; border-top: 1px solid #eee; display: flex; gap: 10px; }
        .chat-input-area input { flex: 1; padding: 12px 20px; border-radius: 25px; border: 1px solid #ddd; outline: none; }
        .send-btn { background: #000; color: #fff; border: none; padding: 10px 20px; border-radius: 25px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        <div style="width: 40px; height: 40px; background: #000; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <?php echo strtoupper(substr($seller_name, 0, 1)); ?>
        </div>
        Chatting with: <?php echo htmlspecialchars($seller_name); ?>
    </div>

    <div class="chat-messages" id="chatArea">
        <!-- Messages will load here -->
    </div>

    <div class="chat-input-area">
        <input type="text" id="userInput" placeholder="Type a message..." autocomplete="off">
        <button class="send-btn" id="sendBtn">Send</button>
    </div>
</div>

<script>
    function loadMessages() {
        $.ajax({
            url: 'load_messages_ajax.php',
            type: 'GET',
            data: { 
                car_id: 0, 
                seller_id: <?php echo $admin_id; ?> // အမြဲတမ်း Admin ဆီပဲ သွားမယ်
            },
            success: function(data) {
                $('#chatArea').html(data);
                let area = document.getElementById("chatArea");
                area.scrollTop = area.scrollHeight;
            }
        });
    }

    function sendMsg() {
        let input = $("#userInput");
        let message = input.val().trim();
        if (message === "") return;

        $.ajax({
            url: 'send_message_ajax.php',
            type: 'POST',
            data: {
                message: message,
                car_id: 0,
                receiver_id: <?php echo $admin_id; ?>,
                is_ajax: 1
            },
            success: function(response) {
                if(response.trim() === "success") {
                    input.val("");
                    loadMessages();
                }
            }
        });
    }

    $(document).ready(function() {
        loadMessages();
        setInterval(loadMessages, 2000);
        $("#sendBtn").click(sendMsg);
        $("#userInput").keypress(function(e) { if (e.which == 13) sendMsg(); });
    });
</script>

</body>
</html>