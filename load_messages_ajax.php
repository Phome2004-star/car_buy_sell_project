<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = db();

// Parameters ယူမယ်
$car_id = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;
$current_user = $_SESSION['user_id'] ?? null;
$other_user_id = 0;

// receiver_id သို့မဟုတ် seller_id တစ်ခုခု ပါလာရင် လက်ခံမယ်
if (isset($_GET['receiver_id'])) {
    $other_user_id = (int)$_GET['receiver_id'];
} elseif (isset($_GET['seller_id'])) {
    $other_user_id = (int)$_GET['seller_id'];
}

// Validation: Login မဝင်ထားရင် သို့မဟုတ် တစ်ဖက်လူ ID မပါရင် ရပ်မယ်
if (!$current_user || $other_user_id === 0) {
    exit("<p style='text-align:center; color:#999; margin-top:20px; font-size:12px;'>Conversation not found.</p>");
}

try {
    // SQL: users table က id ကို စာလုံးအသေး (id) လို့ပဲ သုံးထားတယ်
    // COALESCE ကို သုံးထားတာ ကောင်းပါတယ် (Support chat တွေအတွက်ပါ အလုပ်လုပ်တယ်)
    $query = "
        SELECT m.sender_id, m.receiver_id, m.message_text, m.created_at, u.username as sender_name 
        FROM messages m 
        INNER JOIN users u ON m.sender_id = u.id 
        WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
        AND COALESCE(m.car_id, 0) = ?
        ORDER BY m.created_at ASC
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute([$current_user, $other_user_id, $other_user_id, $current_user, $car_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($messages)) {
        echo '<div style="text-align:center; color:#999; font-size:13px; margin-top:50px;">';
        echo '✨ No conversation yet. <br> Type a message to start!';
        echo '</div>';
    } else {
        foreach ($messages as $msg) {
            $is_me = ($msg['sender_id'] == $current_user);
            $chat_text = $msg['message_text'] ?? "";

            // Styling Logic
            $align = $is_me ? 'text-align: right;' : 'text-align: left;';
            $bg_color = $is_me ? '#000000' : '#e4e6eb'; // Me = Black, Other = Light Gray
            $text_color = $is_me ? '#ffffff' : '#000000';
            $radius = $is_me ? '18px 18px 4px 18px' : '18px 18px 18px 4px';
            $margin = $is_me ? 'margin-left: auto;' : 'margin-right: auto;';
            
            echo '<div style="margin-bottom: 12px; ' . $align . '">';
            
            // Sender Name (တစ်ဖက်လူဆီကလာရင် နာမည်လေးပြရင် ပိုကောင်းတယ်)
            if (!$is_me) {
                echo '<div style="font-size: 10px; color: #888; margin-bottom: 2px; margin-left: 5px;">' . htmlspecialchars($msg['sender_name']) . '</div>';
            }

            echo '<div style="display: inline-block; padding: 10px 16px; border-radius: ' . $radius . '; background: ' . $bg_color . '; color: ' . $text_color . '; max-width: 75%; text-align: left; font-size: 14px; line-height: 1.5; box-shadow: 0 1px 2px rgba(0,0,0,0.05); ' . $margin . '">';
            
            echo nl2br(htmlspecialchars($chat_text)); // nl2br ထည့်လိုက်ရင် message ထဲမှာ Enter ခေါက်ထားတာတွေပါ မြင်ရမယ်
            
            $time_color = $is_me ? 'rgba(255,255,255,0.6)' : 'rgba(0,0,0,0.4)';
            echo '<div style="font-size: 9px; color: ' . $time_color . '; margin-top: 4px; text-align: right;">' . date('h:i A', strtotime($msg['created_at'])) . '</div>';
            
            echo '</div>';
            echo '</div>';
        }
    }

} catch (PDOException $e) {
    // Error တက်ရင် ဘာဖြစ်တာလဲဆိုတာ မြင်ရအောင် debugging နည်းနည်းထည့်ထားတယ်
    echo "<p style='text-align:center; color:#999; font-size:12px;'>Loading error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>