<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$conn = db();

// ၁။ Admin ဟုတ်မဟုတ် စစ်ဆေးခြင်း
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit("Access Denied");
}

// ၂။ User ဖျက်သည့် လုပ်ဆောင်ချက်
if (isset($_GET['delete_user_id'])) {
    $user_id = (int)$_GET['delete_user_id'];
    
    // ကိုယ့်ဘာသာကိုယ် ပြန်မဖျက်မိအောင် စစ်ဆေးခြင်း
    if ($user_id == $_SESSION['user_id']) {
        header("Location: manage_users.php?err=You cannot delete yourself!");
        exit();
    }

    $del_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $del_stmt->execute([$user_id]);
    header("Location: manage_users.php?msg=User removed successfully");
    exit();
}

// ၃။ User အားလုံးကို ဆွဲထုတ်ခြင်း
$users = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-bg: #f0f2f5;
            --sidebar-bg: #1a1c1e;
            --accent-blue: #0984e3;
            --text-main: #2d3436;
            --danger: #ff7675;
            --success: #00b894;
        }

        * { box-sizing: border-box; transition: all 0.3s ease; }
        body { font-family: 'Inter', sans-serif; margin: 0; background: var(--primary-bg); display: flex; color: var(--text-main); }
        
        /* Sidebar */
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
        .logout-btn { background: rgba(255, 118, 117, 0.1); color: var(--danger) !important; margin-top: auto; border: 1px solid rgba(255, 118, 117, 0.2); }
        .logout-btn:hover { background: var(--danger) !important; color: #fff !important; }

        /* Main Content */
        .main-content { flex: 1; padding: 40px; margin-left: 280px; }
        .page-header { margin-bottom: 30px; }
        .page-header h1 { font-size: 28px; font-weight: 700; margin: 0; }
        
        /* Table Design */
        .table-container { 
            background: #fff; padding: 10px; border-radius: 20px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; 
        }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 18px 20px; text-align: left; color: #636e72; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 18px 20px; border-bottom: 1px solid #f1f2f6; vertical-align: middle; font-size: 14px; }

        /* User Specific Styles */
        .user-info { display: flex; align-items: center; gap: 12px; }
        .avatar-circle { 
            width: 40px; height: 40px; background: #dfe6e9; 
            border-radius: 50%; display: flex; align-items: center; 
            justify-content: center; font-weight: bold; color: #636e72;
        }
        .role-badge { 
            padding: 5px 12px; border-radius: 8px; font-size: 11px; 
            font-weight: 700; text-transform: uppercase; 
        }
        .role-admin { background: #fff9db; color: #f08c00; }
        .role-user { background: #e7f5ff; color: #1971c2; }

        /* Buttons */
        .btn-delete { 
            color: var(--danger); text-decoration: none; font-weight: 700; 
            padding: 8px 15px; border-radius: 8px; border: 1px solid transparent;
        }
        .btn-delete:hover { background: rgba(255, 118, 117, 0.1); border-color: var(--danger); }
        .self-tag { color: #b2bec3; font-style: italic; font-size: 12px; }

        /* Alerts */
        .alert { padding: 15px 25px; border-radius: 12px; margin-bottom: 25px; font-weight: 500; }
        .alert-success { background: #dff9fb; color: var(--success); border: 1px solid #55efc4; }
        .alert-error { background: #fff5f5; color: var(--danger); border: 1px solid #ffa8a8; }

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
            <a href="manage_users.php" class="active">👥 <span>Manage Users</span></a>
            <a href="admin_manage_chats.php">💬 <span>Manage Chats</span></a>
            <a href="index.php">🏠 <span>Back to Site</span></a>
        </div>
        <a href="logout.php" class="logout-btn">🚪 <span>Logout</span></a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>User Management</h1>
            <p style="color: #636e72; margin: 5px 0 0;">အဖွဲ့ဝင်များ၏ အချက်အလက်နှင့် Role များကို စီမံခန့်ခွဲခြင်း</p>
        </div>

        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success">✅ <?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>
        
        <?php if(isset($_GET['err'])): ?>
            <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($_GET['err']); ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User Profile</th>
                        <th>Email Address</th>
                        <th>Account Role</th>
                        <th>Joined Since</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                    <tr>
                        <td>
                            <div class="user-info">
                                <div class="avatar-circle">
                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                </div>
                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                            </div>
                        </td>
                        <td style="color: #636e72;"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="role-badge <?php echo ($user['role'] === 'admin') ? 'role-admin' : 'role-user'; ?>">
                                <?php echo $user['role']; ?>
                            </span>
                        </td>
                        <td style="color: #999;">
                            <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                        </td>
                        <td style="text-align: right;">
                            <?php if($user['id'] != $_SESSION['user_id']): ?>
                                <a href="manage_users.php?delete_user_id=<?php echo $user['id']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('ဒီ user ကို ဖျက်မှာ သေချာလား?')">Remove</a>
                            <?php else: ?>
                                <span class="self-tag">(You)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>