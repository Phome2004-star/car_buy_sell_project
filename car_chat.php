<?php
require_once 'db.php';
$conn = db();
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$car_id = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;
$user_id = $_SESSION['user_id']; 

try {
    // ⚠️ စစ်ဆေးရန်: သင်၏ table ထဲတွင် seller_id ဖြစ်စေ user_id ဖြစ်စေ တစ်ခုခုကို သုံးထားပါလိမ့်မည်
    // အောက်ပါ Query တွင် c.seller_id နေရာကို လိုအပ်သလို ပြင်ပါ
    $stmt = $conn->prepare("
        SELECT c.brand, c.model, c.seller_id, u.username 
        FROM car_listings c 
        INNER JOIN users u ON c.seller_id = u.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$car_id]);
    $info = $stmt->fetch();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

if (!$info) {
    // Error တက်ရင် ဘာကြောင့်လဲဆိုတာ သိရအောင် ID ကို ပြပေးထားမယ်
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h3>🚗 Data Not Found!</h3>
            <p>မရရှိနိုင်သော ကား ID ဖြစ်နေပါသည် (ID: $car_id)</p>
            <a href='cars.php' style='color:blue;'>Inventory သို့ ပြန်သွားရန်</a>
         </div>");
}

$seller_id = $info['seller_id'];

if ($user_id == $seller_id) {
    echo "<script>alert('မိမိတင်ထားသောကားကို မိမိပြန် chat လို့မရပါ!'); window.location.href='cars.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($info['username']); ?></title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; }
        .chat-container { max-width: 600px; margin: 20px auto; background: #fff; height: 90vh; display: flex; flex-direction: column; border-radius: 10px; box-shadow: 0 5px 25px rgba(0,0,0,0.1); overflow: hidden; }
        
        .chat-header { padding: 15px 20px; background: #000; color: #fff; display: flex; align-items: center; gap: 15px; }
        .header-info h4 { margin: 0; font-size: 16px; }
        .header-info p { margin: 0; font-size: 12px; color: #bbb; }
        .back-link { color: white; text-decoration: none; font-size: 20px; padding-right: 10px; }
        
        .chat-box { flex: 1; padding: 20px; overflow-y: auto; background: #fdfdfd; display: flex; flex-direction: column; gap: 12px; }
        
        /* Message Bubbles */
        .msg { max-width: 75%; padding: 10px 15px; border-radius: 15px; font-size: 14px; word-wrap: break-word; }
        .sent { align-self: flex-end; background: #000; color: #fff; border-bottom-right-radius: 2px; }
        .received { align-self: flex-start; background: #eee; color: #333; border-bottom-left-radius: 2px; }

        .chat-input { padding: 15px; border-top: 1px solid #eee; display: flex; gap: 10px; background: #fff; }
        .chat-input input { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 25px; outline: none; }
        .chat-input button { background: #000; color: #fff; border: none; padding: 0 25px; border-radius: 25px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .chat-input button:hover { background: #333; }
        .chat-input button:disabled { background: #999; }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        <a href="car_details.php?id=<?php echo $car_id; ?>" class="back-link">❮</a>
        <div style="background: #333; width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 1px solid #444;">
            <?php echo strtoupper(substr($info['username'], 0, 1)); ?>
        </div>
        <div class="header-info">
            <h4><?php echo htmlspecialchars($info['username']); ?> (Seller)</h4>
            <p><?php echo htmlspecialchars($info['brand'] . " " . $info['model']); ?></p>
        </div>
    </div>

    <div class="chat-box" id="chatArea">
        <p style="text-align:center; color:#999; font-size:12px;">Loading messages...</p>
    </div>

    <div class="chat-input">
        <input type="text" id="msgInput" placeholder="Write your message..." autocomplete="off">
        <button id="sendBtn">Send</button>
    </div>
</div>

<script>
    function loadMessages() {
        $.get('load_messages_ajax.php', { 
            car_id: <?php echo $car_id; ?>, 
            seller_id: <?php echo $seller_id; ?> 
        }, function(data) {
            $('#chatArea').html(data);
            var chatBox = document.getElementById("chatArea");
            chatBox.scrollTop = chatBox.scrollHeight;
        }).fail(function() {
            console.log("Error loading messages");
        });
    }

    function sendMsg() {
        let text = $('#msgInput').val().trim();
        if (text === "") return;

        $('#sendBtn').prop('disabled', true); 

        $.post('send_message_ajax.php', {
            message: text,
            car_id: <?php echo $car_id; ?>,
            receiver_id: <?php echo $seller_id; ?>,
            is_ajax: 1
        }, function(res) {
            $('#sendBtn').prop('disabled', false);
            if (res.trim() === "success") {
                $('#msgInput').val('');
                loadMessages();
            } else {
                alert("Server Response: " + res);
            }
        });
    }

    $(document).ready(function() {
        loadMessages();
        // ၃ စက္ကန့်တစ်ခါ update လုပ်မယ်
        setInterval(loadMessages, 3000);

        $('#sendBtn').click(sendMsg);
        $('#msgInput').keypress(function(e) { 
            if(e.which == 13) sendMsg(); 
        });
    });
</script>

</body>
</html>