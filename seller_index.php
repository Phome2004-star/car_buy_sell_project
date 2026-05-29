<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = db();

// Seller ဟုတ်မဟုတ် စစ်ဆေးခြင်း
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

// ကားစာရင်းဆွဲထုတ်ခြင်း
try {
    $stmt = $conn->prepare("SELECT * FROM car_listings WHERE seller_id = ? ORDER BY id DESC");
    $stmt->execute([$seller_id]);
    $my_cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $my_cars = [];
}

// Buyer Message များဆွဲထုတ်ခြင်း
try {
    $msg_stmt = $conn->prepare("
        SELECT m.*, u.username as buyer_name, c.brand, c.model 
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        JOIN car_listings c ON m.car_id = c.id
        WHERE m.receiver_id = ?
        GROUP BY m.sender_id, m.car_id
        ORDER BY m.id DESC LIMIT 5
    ");
    $msg_stmt->execute([$seller_id]);
    $recent_chats = $msg_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_chats = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard | Phome Htet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f8f9fa;
            --sidebar-bg: #1a1c1e;
            --accent-blue: #0984e3;
            --text-dark: #2d3436;
            --white: #ffffff;
            --danger: #ff7675;
            --success: #00b894;
        }

        * { box-sizing: border-box; transition: all 0.3s ease; }
        body { font-family: 'Inter', sans-serif; margin: 0; background: var(--bg-color); color: var(--text-dark); display: flex; min-height: 100vh; }

        /* Sidebar Style */
        .sidebar { 
            width: 280px; background: var(--sidebar-bg); color: #fff; 
            height: 100vh; padding: 30px 20px; position: fixed; 
            display: flex; flex-direction: column;
        }
        .sidebar h2 { font-size: 1.5rem; text-align: center; margin-bottom: 40px; font-weight: 800; }
        .sidebar h2 span { color: #FFD700; }
        .nav-links { flex-grow: 1; }
        .sidebar a { 
            color: #b2bec3; display: flex; align-items: center; 
            padding: 14px 20px; text-decoration: none; border-radius: 12px; 
            margin-bottom: 8px; font-weight: 500;
        }
        .sidebar a:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .sidebar a.active { background: var(--accent-blue); color: #fff; box-shadow: 0 4px 15px rgba(9, 132, 227, 0.2); }
        .logout-btn { background: rgba(255, 118, 117, 0.1); color: var(--danger) !important; margin-top: auto; border: 1px solid rgba(255, 118, 117, 0.2); }
        .logout-btn:hover { background: var(--danger) !important; color: #fff !important; }

        /* Main Content */
        .main-content { flex: 1; padding: 40px; margin-left: 280px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .page-header h1 { font-size: 28px; font-weight: 700; margin: 0; }
        .add-btn { background: var(--accent-blue); color: #fff; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 12px rgba(9, 132, 227, 0.2); }
        .add-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(9, 132, 227, 0.3); }

        /* Message Cards */
        .section-title { font-size: 18px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .msg-container { display: grid; gap: 15px; margin-bottom: 40px; }
        .msg-card { 
            background: var(--white); padding: 20px; border-radius: 16px; 
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #edf2f7;
        }
        .msg-info h4 { margin: 0 0 5px; font-size: 15px; color: var(--accent-blue); }
        .msg-info p { margin: 0; font-size: 14px; color: #636e72; }
        .reply-link { color: var(--accent-blue); text-decoration: none; font-weight: 700; font-size: 14px; padding: 8px 16px; border-radius: 8px; background: #e3f2fd; }
        .reply-link:hover { background: var(--accent-blue); color: #fff; }

        /* Table Style */
        .table-card { background: var(--white); border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.03); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 18px 25px; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #a0aec0; }
        td { padding: 20px 25px; border-bottom: 1px solid #f1f2f6; vertical-align: middle; }
        
        .car-info { display: flex; align-items: center; gap: 15px; }
        .car-img { width: 60px; height: 45px; border-radius: 8px; object-fit: cover; background: #eee; }
        .car-name { font-weight: 600; font-size: 15px; display: block; }
        .car-year { font-size: 12px; color: #b2bec3; }

        .price-text { font-weight: 700; color: var(--text-dark); }
        
        .status-badge { 
            padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase;
        }
        .status-available { background: #e6fffa; color: var(--success); }
        .status-sold { background: #fff5f5; color: var(--danger); }

        .action-links a { text-decoration: none; font-size: 13px; font-weight: 600; margin-right: 15px; }
        .edit-link { color: var(--text-dark); }
        .delete-link { color: var(--danger); }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar h2, .sidebar a span { display: none; }
            .main-content { margin-left: 80px; }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Phome Htet <span>Seller</span></h2>
        <div class="nav-links">
            <a href="seller_index.php" class="active">📊 <span>My Dashboard</span></a>
            <a href="add_car.php">🚗 <span>Post New Car</span></a>
            <a href="index.php">🏠 <span>View Website</span></a>
        </div>
        <a href="logout.php" class="logout-btn">🚪 <span>Logout</span></a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1>Dashboard Overview</h1>
                <p style="color: #636e72; margin-top: 5px;">သင့်ကားအရောင်းစာရင်းနှင့် မက်ဆေ့ချ်များကို စီမံခန့်ခွဲပါ</p>
            </div>
            <a href="add_car.php" class="add-btn">+ Post a Car</a>
        </div>

        <!-- Messages Section -->
        <div class="section-title">📩 Recent Inquiries</div>
        <div class="msg-container">
            <?php if (!empty($recent_chats)): ?>
                <?php foreach ($recent_chats as $chat): ?>
                    <div class="msg-card">
                        <div class="msg-info">
                            <h4><?php echo htmlspecialchars($chat['buyer_name']); ?></h4>
                            <p>Regarding: <strong><?php echo htmlspecialchars($chat['brand'] . ' ' . $chat['model']); ?></strong></p>
                        </div>
                        <a href="chat.php?car_id=<?php echo $chat['car_id']; ?>&receiver_id=<?php echo $chat['sender_id']; ?>" class="reply-link">Reply Chat</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; background: #fff; border-radius: 16px; color: #b2bec3; border: 1px dashed #cbd5e0;">
                    No messages from buyers yet.
                </div>
            <?php endif; ?>
        </div>

        <!-- Listings Section -->
        <div class="section-title">🚗 My Listings (<?php echo count($my_cars); ?>)</div>
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Car Model</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th style="text-align: right;">Management</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($my_cars)): ?>
                        <?php foreach ($my_cars as $car): ?>
                            <tr>
                                <td>
                                    <div class="car-info">
                                        <img src="<?php echo htmlspecialchars($car['image_url']); ?>" class="car-img" alt="car">
                                        <div>
                                            <span class="car-name"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></span>
                                            <span class="car-year">Year: <?php echo $car['year']; ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="price-text"><?php echo number_format($car['price']); ?> MMK</span></td>
                                <td>
                                    <span class="status-badge <?php echo (strtolower($car['status'] ?? '') == 'sold out') ? 'status-sold' : 'status-available'; ?>">
                                        <?php echo strtoupper($car['status'] ?? 'AVAILABLE'); ?>
                                    </span>
                                </td>
                                <td class="action-links" style="text-align: right;">
                                    <a href="car_details.php?id=<?php echo $car['id']; ?>" style="color: var(--accent-blue);">View</a>
                                    <a href="edit_car.php?id=<?php echo $car['id']; ?>" class="edit-link">Edit</a>
                                    <a href="delete_car.php?id=<?php echo $car['id']; ?>" class="delete-link" onclick="return confirm('ဖျက်မှာ သေချာလား?')">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 50px; color: #b2bec3;">You haven't posted any cars yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>