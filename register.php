<?php
require_once 'db.php';
$conn = db();

// Admin ဖြစ်ဖို့ သတ်မှတ်ထားတဲ့ လျှို့ဝှက်ကုဒ်
$SECRET_ADMIN_KEY = "ADMIN123"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role']; // 'buyer', 'seller' သို့မဟုတ် 'admin'
    $nrc = ($role === 'seller') ? $_POST['nrc'] : NULL;
    $admin_key = $_POST['admin_key'] ?? '';
    $saved_admin_key = ($role === 'admin') ? $admin_key : NULL;

    try {
        // အီးမေးလ် စစ်ဆေးခြင်း
        $checkEmail = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $checkEmail->execute([$email]);
        
        if ($checkEmail->fetch()) {
            $error = "ဤအီးမေးလ်ဖြင့် အကောင့်ဖွင့်ပြီးသား ဖြစ်နေပါသည်။";
        } elseif ($role === 'admin' && $admin_key !== $SECRET_ADMIN_KEY) {
            // Admin ရွေးထားပြီး Key မှားရင် လက်မခံဘူး
            $error = "Admin လျှို့ဝှက်ကုဒ် မှားယွင်းနေပါသည်။";
        } else {
            // Database ထဲမှာ nrc နဲ့ admin_secret_key ပါ ထည့်သွင်းမယ်
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, nrc, admin_secret_key) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $password, $role, $nrc, $saved_admin_key]);
            
            echo "<script>alert('Account created successfully as " . ucfirst($role) . "!'); window.location='login.php';</script>";
            exit();
        }
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Car Sales System</title>
    <link rel="stylesheet" href="style.css?v=2.0">
    <style>
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .input-group input, .input-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .auth-container { max-width: 450px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .auth-btn { width: 100%; padding: 12px; background:rgb(3, 4, 5); color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        .auth-btn:hover { background: #74b9ff; }
    </style>
    <script>
        function toggleFields() {
            var role = document.getElementById("role").value;
            var nrcField = document.getElementById("nrc_container");
            var adminField = document.getElementById("admin_key_container");
            
            nrcField.style.display = (role === "seller") ? "block" : "none";
            adminField.style.display = (role === "admin") ? "block" : "none";

            // Required field တွေကို dynamic ပြောင်းမယ်
            document.getElementsByName("nrc")[0].required = (role === "seller");
            document.getElementsByName("admin_key")[0].required = (role === "admin");
        }
    </script>
</head>
<body style="background: #f1f2f6; font-family: sans-serif;">

    <div class="auth-container">
        <h2 style="text-align:center; color:#2d3436;">Create Account</h2>
        <p style="text-align:center; color:#636e72; margin-bottom:20px;">Join our Marketplace</p>
        
        <?php if(isset($error)): ?>
            <div style="background: #fab1a0; color: #d63031; padding: 10px; border-radius: 5px; text-align: center; margin-bottom: 15px; font-size: 14px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="username" placeholder="Full Name" required>
            </div>

            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="example@mail.com" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Min 8 characters" required>
            </div>
            
            <div class="input-group">
                <label>Register as</label>
                <select name="role" id="role" onchange="toggleFields()">
                    <option value="buyer">Buyer (ကားကြည့်မည့်သူ)</option>
                    <option value="seller">Seller (ကားရောင်းမည့်သူ)</option>
                    <option value="admin">System Admin (စီမံခန့်ခွဲသူ)</option>
                </select>
            </div>
            
            <div id="nrc_container" class="input-group" style="display: none;">
                <label>NRC Number</label>
                <input type="text" name="nrc" placeholder="Format: 12/YAKANA(N)123456">
            </div>

            <div id="admin_key_container" class="input-group" style="display: none;">
                <label style="color: #d63031;">Admin Secret Key</label>
                <input type="password" name="admin_key" placeholder="Enter Admin Password">
            </div>
            
            <button type="submit" class="auth-btn">Register Now</button>
        </form>
        
        <p style="text-align:center; margin-top:20px; font-size: 14px;">
            Already have an account? <a href="login.php" style="color: #0984e3; text-decoration: none; font-weight: bold;">Login here</a>
        </p>
    </div>

</body>
</html>