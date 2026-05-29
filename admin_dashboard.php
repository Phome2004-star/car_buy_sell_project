<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$conn = db();

// ၁။ Admin ဟုတ်မဟုတ် စစ်ဆေးခြင်း
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit("Access Denied");
}

try {
    $total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_cars = $conn->query("SELECT COUNT(*) FROM car_listings")->fetchColumn();
    $total_messages = $conn->query("SELECT COUNT(*) FROM messages")->fetchColumn();
} catch (PDOException $e) {
    $total_users = 0;
    $total_cars = 0;
    $total_messages = 0;
    $db_error = "Database Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Phome Htet Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-bg: #f0f2f5;
            --sidebar-bg: #1a1c1e;
            --accent-blue: #0984e3;
            --text-main: #2d3436;
            --card-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        * { box-sizing: border-box; transition: all 0.3s ease; }
        body { font-family: 'Inter', sans-serif; margin: 0; background: var(--primary-bg); display: flex; color: var(--text-main); }
        
        /* Sidebar Styles */
        .sidebar { 
            width: 280px; background: var(--sidebar-bg); color: #fff; 
            height: 100vh; padding: 30px 20px; position: fixed; 
            display: flex; flex-direction: column;
        }
        .sidebar h2 { font-size: 1.5rem; font-weight: 800; margin-bottom: 40px; text-align: center; }
        .sidebar h2 span { color: #FFD700; }

        .nav-links { flex-grow: 1; }
        .sidebar a { 
            color: #b2bec3; display: flex; align-items: center; 
            padding: 14px 20px; text-decoration: none; border-radius: 12px; 
            margin-bottom: 10px; font-weight: 500;
        }
        .sidebar a:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .sidebar a.active { background: var(--accent-blue); color: #fff; box-shadow: 0 4px 15px rgba(9, 132, 227, 0.3); }
        
        .logout-btn { 
            background: rgba(255, 118, 117, 0.1); color: #ff7675 !important; 
            margin-top: auto; border: 1px solid rgba(255, 118, 117, 0.2); 
        }
        .logout-btn:hover { background: #ff7675 !important; color: #fff !important; }

        /* Main Content */
        .main-content { flex: 1; padding: 40px; margin-left: 280px; }
        .header-title { margin-bottom: 40px; }
        .header-title h1 { font-size: 32px; font-weight: 800; margin: 0; }
        .header-title p { color: #636e72; margin: 8px 0 0; font-size: 16px; }
        
        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; }
        
        .card { 
            background: #fff; padding: 35px; border-radius: 24px; 
            box-shadow: var(--card-shadow); border: none; 
            display: flex; flex-direction: column; justify-content: space-between;
        }
        
        .card-icon {
            width: 50px; height: 50px; border-radius: 15px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; margin-bottom: 20px;
        }

        .card.users .card-icon { background: rgba(108, 92, 231, 0.1); color: #6c5ce7; }
        .card.cars .card-icon { background: rgba(0, 206, 201, 0.1); color: #00cec9; }
        .card.msgs .card-icon { background: rgba(9, 132, 227, 0.1); color: #0984e3; }

        .card h3 { color: #636e72; font-size: 14px; text-transform: uppercase; letter-spacing: 1.2px; margin: 0; font-weight: 700; }
        .card .value { font-size: 42px; font-weight: 800; color: var(--text-main); margin: 10px 0; }
        .card .info { font-size: 14px; color: #b2bec3; font-weight: 500; }

        .error-alert { background: #fff5f5; color: #ff7675; padding: 15px 25px; border-radius: 12px; border: 1px solid #ffa8a8; margin-bottom: 25px; }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar h2, .sidebar a span { display: none; }
            .main-content { margin-left: 80px; }
        }

        @media (max-width: 768px) {
            .main-content { padding: 25px; }
            .header-title h1 { font-size: 26px; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Phome Htet <span>Admin</span></h2>
        
        <div class="nav-links">
            <a href="admin_dashboard.php" class="active">📊 <span>Dashboard</span></a>
            <a href="admin_manage_car.php">🚗 <span>Manage Cars</span></a>
            <a href="manage_users.php">👥 <span>Manage Users</span></a>
            <a href="admin_manage_chats.php">💬 <span>Manage Chats</span></a>
            <a href="index.php">🏠 <span>Back to Site</span></a>
        </div>

        <a href="logout.php" class="logout-btn">🚪 <span>Logout</span></a>
    </div>

    <div class="main-content">
        <div class="header-title">
            <h1>စနစ်၏ အခြေအနေ</h1>
            <p>မင်္ဂလာပါ Admin။ ဒီနေ့အတွက် Platform ရဲ့ အချက်အလက်အကျဉ်းချုပ်ကို ဒီမှာ ကြည့်နိုင်ပါတယ်။</p>
        </div>
        
        <?php if(isset($db_error)): ?>
            <div class="error-alert">⚠️ <?php echo $db_error; ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <!-- Users Card -->
            <div class="card users">
                <div>
                    <div class="card-icon">👥</div>
                    <h3>Total Users</h3>
                    <div class="value"><?php echo number_format($total_users); ?></div>
                </div>
                <div class="info">စနစ်အတွင်းရှိ အသုံးပြုသူ စုစုပေါင်း</div>
            </div>

            <!-- Cars Card -->
            <div class="card cars">
                <div>
                    <div class="card-icon">🚗</div>
                    <h3>Active Listings</h3>
                    <div class="value"><?php echo number_format($total_cars); ?></div>
                </div>
                <div class="info">ရောင်းရန်တင်ထားသော ကားအရည်အတွက်</div>
            </div>

            <!-- Messages Card -->
            <div class="card msgs">
                <div>
                    <div class="card-icon">💬</div>
                    <h3>Conversations</h3>
                    <div class="value"><?php echo number_format($total_messages); ?></div>
                </div>
                <div class="info">ပေးပို့ထားသော မက်ဆေ့ချ် စုစုပေါင်း</div>
            </div>
        </div>

        <!-- Future Sections (Activity Logs, etc.) could go here -->
    </div>
</body>
</html>