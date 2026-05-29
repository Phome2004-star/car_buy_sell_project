<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = db();

// POST Request နဲ့ Login ဝင်ထားမှ အလုပ်လုပ်မယ်
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    
    $sender_id = $_SESSION['user_id'];
    $car_id = isset($_POST['car_id']) ? (int)$_POST['car_id'] : 0;
    $receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // AJAX ဟုတ်မဟုတ် စစ်တာကို ပိုသေချာအောင် ရေးထားတယ်
    $is_ajax = isset($_POST['is_ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');

    // ကိုယ့်ဘာသာကိုယ် message ပို့တာ တားမယ်
    if ($sender_id === $receiver_id) {
        if ($is_ajax) { 
            echo "error: You cannot message yourself."; 
            exit(); 
        }
        header("Location: cars.php");
        exit();
    }

    // Message မလွတ်ရဘူး၊ Receiver ID ရှိရမယ်
    if (!empty($message) && $receiver_id > 0) {
        try {
            // car_id က 0 ဖြစ်နေရင် DB ထဲမှာ null အဖြစ် သိမ်းမယ် (Support chat အတွက်)
            $db_car_id = ($car_id > 0) ? $car_id : null;

            // Database Column names တွေကို တစ်ချက်ပြန်စစ်ပါ (ဥပမာ- message_text လား message လား)
            $stmt = $conn->prepare("INSERT INTO messages (car_id, sender_id, receiver_id, message_text) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$db_car_id, $sender_id, $receiver_id, $message]);
            
            if ($is_ajax) {
                if ($result) {
                    echo "success";
                } else {
                    echo "error: Failed to insert message";
                }
                exit();
            }

            // AJAX မဟုတ်ရင် Redirect ပြန်လုပ်မယ်
            header("Location: car_chat.php?car_id=$car_id");
            exit();
            
        } catch (PDOException $e) {
            if ($is_ajax) {
                http_response_code(500);
                echo "error: " . $e->getMessage();
                exit();
            }
            die("Database Error: " . $e->getMessage());
        }
    } else {
        if ($is_ajax) {
            echo "error: Empty message or invalid receiver.";
            exit();
        }
        header("Location: cars.php");
        exit();
    }
} else {
    // Login မဝင်ထားရင် သို့မဟုတ် တိုက်ရိုက်ဝင်ကြည့်ရင် Login ကို လွှတ်မယ်
    header("Location: login.php");
    exit();
}
?>