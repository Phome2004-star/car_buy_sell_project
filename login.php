<?php
require_once 'db.php';
// အရေးကြီးဆုံးအချက်- Session ကို အပေါ်ဆုံးမှာ စဖွင့်ရပါမယ်
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$conn = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = strtolower($user['role']); 
        
        // Role အလိုက် Dashboard များကို ခွဲပို့ခြင်း
        if ($_SESSION['role'] === 'admin') {
            header("Location: admin_dashboard.php"); 
        } elseif ($_SESSION['role'] === 'seller') {
            header("Location: seller_index.php"); 
        } else {
            header("Location: index.php"); // Buyer ဆိုရင် Home ကို ပို့မယ်
        }
        exit();
    } else {
        $error = "အီးမေးလ် သို့မဟုတ် စကားဝှက် မှားယွင်းနေပါသည်။";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Phome Htet Car Sales</title>
    <link rel="stylesheet" href="style.css?v=2.0">
</head>
<body class="auth-body">

    <div class="auth-container">
        <h2 class="auth-title">Welcome Back</h2>
        
        <?php if(isset($error)): ?>
            <p class="error-msg" style="background: #ff7675; color: white; padding: 10px; border-radius: 5px; text-align: center; margin-bottom: 15px;">
                <?php echo $error; ?>
            </p>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="email@example.com" required>
            </div>
            
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="auth-btn">Sign In</button>
        </form>

        <p class="auth-footer">
            New to Phome Htet? <a href="register.php">Create an account</a>
        </p>
    </div>

</body>
</html>