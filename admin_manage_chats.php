<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$conn = db();

// ၁။ Admin ဟုတ်မဟုတ် စစ်ဆေးခြင်း
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit("Access Denied");
}

// ၂။ Message တစ်ခုချင်းစီ ဖျက်သည့် Logic
if (isset($_GET['delete_msg_id'])) {
    $msg_id = (int)$_GET['delete_msg_id'];
    $del_stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $del_stmt->execute([$msg_id]);
    header("Location: admin_manage_chats.php?msg=Message deleted successfully");
    exit();
}

// ၃။ Chat Messages အားလုံးကို User names တွေ၊ Car details တွေနဲ့ Join ပြီး ဆွဲထုတ်ခြင်း
$chats_query = "
    SELECT 
        m.id, 
        m.message_text, 
        m.created_at, 
        u1.username AS sender, 
        u2.username AS receiver, 
        c.brand, 
        c.model 
    FROM messages m
    JOIN users u1 ON m.sender_id = u1.id
    JOIN users u2 ON m.receiver_id = u2.id
    LEFT JOIN car_listings c ON m.car_id = c.id
    ORDER BY m.created_at DESC
";
$chats = $conn->query($chats_query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Chats | Admin Panel</title>
    <style>
        :root {
            --primary-bg: #f0f2f5;
            --sidebar-bg: #1a1c1e;
            --accent-blue: #0984e3;
            --text-main: #2d3436;
        }

        * { box-sizing: border-box; transition: all 0.3s ease; }
        body { font-family: 'Inter', sans-serif; margin: 0; background: var(--primary-bg); display: flex; }
        
        /* Sidebar (Matching Global Style) */
        .sidebar { 
            width: 280px; background: var(--sidebar-bg); color: #fff; 
            height: 100vh; padding: 30px 20px; position: fixed; 
            display: flex; flex-direction: column;
        }
        .sidebar h2 { font-size: 1.5rem; text-align: center; margin-bottom: 40px; }
        .sidebar h2 span { color: #FFD700; }
        .nav-links { flex-grow: 1; }
        .sidebar a { 
            color: #b2bec3; display: flex; align-items: center; 
            padding: 14px 20px; text-decoration: none; border-radius: 12px; 
            margin-bottom: 10px; font-weight: 500;
        }
        .sidebar a:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .sidebar a.active { background: var(--accent-blue); color: #fff; box-shadow: 0 4px 15px rgba(9, 132, 227, 0.3); }
        .logout-btn { background: rgba(255, 118, 117, 0.1); color: #ff7675 !important; margin-top: auto; border: 1px solid rgba(255, 118, 117, 0.2); }
        .logout-btn:hover { background: #ff7675 !important; color: #fff !important; }

        /* Main Content */
        .main-content { flex: 1; padding: 40px; margin-left: 280px; }
        .page-header { margin-bottom: 30px; }
        .page-header h1 { font-size: 28px; color: var(--text-main); margin: 0; }
        
        /* Table Container */
        .table-container { 
            background: #fff; padding: 10px; border-radius: 20px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; 
        }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 18px 20px; text-align: left; color: #636e72; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 18px 20px; border-bottom: 1px solid #f1f2f6; vertical-align: middle; color: #2d3436; font-size: 14px; }
        
        .user-pill { 
            display: inline-block; padding: 4px 10px; background: #e3f2fd; 
            color: #1976d2; border-radius: 6px; font-weight: 600; font-size: 13px;
        }
        .car-tag { 
            font-size: 11px; font-weight: 700; color: #636e72; 
            background: #f1f2f6; padding: 4px 8px; border-radius: 4px; 
        }
        .msg-bubble { 
            max-width: 300px; line-height: 1.5; color: #4b4b4b; 
            word-break: break-word; font-style: italic;
        }
        
        .btn-delete { 
            color: #ff7675; text-decoration: none; font-weight: 700; 
            padding: 8px 12px; border: 1px solid transparent; border-radius: 8px;
        }
        .btn-delete:hover { background: rgba(255, 118, 117, 0.1); border-color: #ff7675; }

        .alert { background: #dff9fb; color: #00b894; padding: 15px 25px; border-radius: 12px; border: 1px solid #55efc4; margin-bottom: 25px; }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar h2, .sidebar a span { display: none; }
            .main-content { margin-left: 80px; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Phome Htet <span>Admin</span></h2>
        <div class="nav-links">
            <a href="admin_dashboard.php">📊 <span>Dashboard</span></a>
            <a href="admin_manage_car.php">🚗 <span>Manage Cars</span></a>
            <a href="manage_users.php">👥 <span>Manage Users</span></a>
            <a href="admin_manage_chats.php" class="active">💬 <span>Manage Chats</span></a>
            <a href="index.php">🏠 <span>Back to Site</span></a>
        </div>
        <a href="logout.php" class="logout-btn">🚪 <span>Logout</span></a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Chat Monitoring</h1>
            <p style="color: #636e72; margin: 5px 0 0;">Platform ပေါ်ရှိ စကားပြောဆိုမှုများကို စောင့်ကြည့်ထိန်းချုပ်ခြင်း</p>
        </div>

        <?php if(isset($_GET['msg'])): ?>
            <div class="alert">✅ <?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Participants</th>
                        <th>Car Context</th>
                        <th>Message Content</th>
                        <th>Timestamp</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($chats) > 0): ?>
                        <?php foreach($chats as $chat): ?>
                        <tr>
                            <td>
                                <div style="display:flex; flex-direction:column; gap:5px;">
                                    <div><small style="color:#b2bec3;">From:</small> <span class="user-pill"><?php echo htmlspecialchars($chat['sender']); ?></span></div>
                                    <div><small style="color:#b2bec3;">To:</small> <span class="user-pill" style="background:#f5f5f5; color:#616161;"><?php echo htmlspecialchars($chat['receiver']); ?></span></div>
                                </div>
                            </td>
                            <td>
                                <?php if($chat['brand']): ?>
                                    <span class="car-tag">🚘 <?php echo htmlspecialchars($chat['brand'] . ' ' . $chat['model']); ?></span>
                                <?php else: ?>
                                    <span style="color:#ccc; font-size:11px;">General Inquiry</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="msg-bubble">"<?php echo htmlspecialchars($chat['message_text']); ?>"</div>
                            </td>
                            <td>
                                <small style="color: #999; font-weight: 500;">
                                    <?php echo date('M d, Y', strtotime($chat['created_at'])); ?><br>
                                    <?php echo date('h:i A', strtotime($chat['created_at'])); ?>
                                </small>
                            </td>
                            <td style="text-align: right;">
                                <a href="admin_manage_chats.php?delete_msg_id=<?php echo $chat['id']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('ဒီ message ကို ဖျက်မှာ သေချာလား?')">Remove</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #999; padding: 50px;">
                                <div style="font-size: 40px; margin-bottom: 10px;">💬</div>
                                No chat messages found in the system.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>