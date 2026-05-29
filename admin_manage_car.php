<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$conn = db();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit("Access Denied");
}

// ဖျက်တဲ့ Logic
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    $img_stmt = $conn->prepare("SELECT image_url FROM car_listings WHERE id = ?");
    $img_stmt->execute([$delete_id]);
    $car_img = $img_stmt->fetchColumn();
    
    if ($car_img && file_exists($car_img)) {
        unlink($car_img);
    }

    $del_stmt = $conn->prepare("DELETE FROM car_listings WHERE id = ?");
    $del_stmt->execute([$delete_id]);
    header("Location: admin_manage_car.php?msg=Car Deleted Successfully");
    exit();
}

$cars = $conn->query("
    SELECT c.*, u.username as seller_name 
    FROM car_listings c 
    LEFT JOIN users u ON c.seller_id = u.id 
    ORDER BY c.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory | Admin Panel</title>
    <style>
        :root {
            --primary-bg: #f0f2f5;
            --sidebar-bg: #1a1c1e;
            --accent-blue: #0984e3;
            --text-main: #2d3436;
        }

        * { box-sizing: border-box; transition: all 0.3s ease; }
        body { font-family: 'Inter', sans-serif; margin: 0; background: var(--primary-bg); display: flex; }
        
        /* Sidebar (Global Consistency) */
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
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-header h1 { font-size: 28px; color: var(--text-main); margin: 0; }

        /* Table Styling */
        .table-container { 
            background: #fff; padding: 10px; border-radius: 20px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; 
        }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 18px 20px; text-align: left; color: #636e72; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 15px 20px; border-bottom: 1px solid #f1f2f6; vertical-align: middle; color: #2d3436; }

        /* Car Image Thumbnail */
        .car-thumb-container { position: relative; width: 80px; height: 55px; }
        .car-thumb { width: 100%; height: 100%; object-fit: cover; border-radius: 10px; border: 1px solid #eee; }
        
        /* Price Tag */
        .price-tag { color: #2ecc71; font-weight: 800; font-size: 15px; }

        /* Status Badge */
        .status-badge { 
            padding: 5px 12px; border-radius: 50px; font-size: 10px; 
            font-weight: 800; text-transform: uppercase;
        }
        .status-available { background: #e3fcef; color: #00b894; }
        .status-sold { background: #fab1a0; color: #d63031; }

        /* Delete Button */
        .btn-delete { 
            background: #fff; color: #ff7675; border: 1px solid #ff7675; 
            padding: 8px 16px; border-radius: 8px; text-decoration: none; 
            font-size: 12px; font-weight: 600;
        }
        .btn-delete:hover { background: #ff7675; color: #fff; transform: scale(1.05); }

        /* Success Message */
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
            <a href="admin_manage_car.php" class="active">🚗 <span>Manage Cars</span></a>
            <a href="manage_users.php">👥 <span>Manage Users</span></a>
            <a href="admin_manage_chats.php">💬 <span>Manage Chats</span></a>
            <a href="index.php">🏠 <span>Back to Site</span></a>
        </div>
        <a href="logout.php" class="logout-btn">🚪 <span>Logout</span></a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Car Inventory</h1>
            <div style="font-size: 14px; color: #636e72;">Found <?php echo count($cars); ?> listings</div>
        </div>

        <?php if(isset($_GET['msg'])): ?>
            <div class="alert">✨ <?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Details</th>
                        <th>Price</th>
                        <th>Seller</th>
                        <th>Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($cars as $car): ?>
                    <tr>
                        <td width="100">
                            <div class="car-thumb-container">
                                <?php if(!empty($car['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($car['image_url']); ?>" class="car-thumb">
                                <?php else: ?>
                                    <div class="car-thumb" style="background:#eee; display:flex; align-items:center; justify-content:center; font-size:10px; color:#999;">No Image</div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <strong style="font-size: 16px;"><?php echo htmlspecialchars($car['brand']); ?></strong><br>
                            <span style="color: #636e72; font-size: 13px;"><?php echo htmlspecialchars($car['model']); ?> • <?php echo $car['year']; ?></span>
                        </td>
                        <td>
                            <span class="price-tag"><?php echo number_format($car['price']); ?></span> 
                            <small style="color: #b2bec3; font-weight: bold;">MMK</small>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 24px; height: 24px; background: #dfe6e9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold;">
                                    <?php echo strtoupper(substr($car['seller_name'] ?? 'U', 0, 1)); ?>
                                </div>
                                <span style="font-size: 14px;"><?php echo htmlspecialchars($car['seller_name'] ?? 'Unknown'); ?></span>
                            </div>
                        </td>
                        <td>
                            <?php 
                                $status = strtolower($car['status'] ?? 'available');
                                $status_class = ($status === 'sold') ? 'status-sold' : 'status-available';
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <a href="admin_manage_car.php?delete_id=<?php echo $car['id']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this listing? This action cannot be undone.')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>