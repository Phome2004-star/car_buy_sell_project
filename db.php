<?php
// ၁။ Session ကို ဖိုင်စဖွင့်တာနဲ့ တန်းစစ်မယ် (ဒါမှ ဖိုင်တိုင်းမှာ လိုက်ရေးစရာမလိုတော့ပါ)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function db() {
    $host = 'localhost';
    $db   = 'cars'; 
    $user = 'root';
    $pass = ''; 
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    try {
         return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
         ]);
    } catch (\PDOException $e) {
         die("Database Connection Failed: " . $e->getMessage());
    }
}

// ဒီ function ကို လိုအပ်ရင် သုံးဖို့ ဆက်ထားပေးထားပါတယ်
function ensure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// User က Login ဝင်ထားသလား စစ်တဲ့ Shortcut Function (Option)
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// User က Seller လား စစ်တဲ့ Shortcut Function (Option)
function is_seller() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'seller';
}
?>