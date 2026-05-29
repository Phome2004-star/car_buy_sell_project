<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$conn = db();

// Admin Delete Logic
if (isset($_GET['delete_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $delete_id = (int)$_GET['delete_id'];
    
    $stmt = $conn->prepare("SELECT image_url FROM car_listings WHERE id = ?");
    $stmt->execute([$delete_id]);
    $car_img = $stmt->fetchColumn();
    
    if ($car_img && file_exists($car_img)) {
        unlink($car_img);
    }

    $conn->prepare("DELETE FROM car_listings WHERE id = ?")->execute([$delete_id]);
    header("Location: cars.php?msg=Car Deleted Successfully");
    exit();
}

// Search & Filter Logic
$where_clauses = [];
$params = [];

if (isset($_GET['brand']) && !empty($_GET['brand'])) {
    $where_clauses[] = "brand = ?";
    $params[] = $_GET['brand'];
}

if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $where_clauses[] = "price <= ?";
    $params[] = $_GET['max_price'];
}

$sql = "SELECT * FROM car_listings";
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}
$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Cars | Phome Htet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a1c1e;
            --accent: #0984e3;
            --bg: #f8f9fa;
            --white: #ffffff;
            --text-main: #2d3436;
            --border: #edf2f7;
        }

        body { background: var(--bg); color: var(--text-main); font-family: 'Inter', sans-serif; margin: 0; }
        
        /* Navbar */
        .navbar { 
            background: var(--primary); padding: 15px 8%; display: flex; 
            justify-content: space-between; align-items: center; 
            position: sticky; top: 0; z-index: 1000;
        }
        .navbar .logo { color: #fff; font-size: 20px; font-weight: 800; text-decoration: none; }
        .navbar .logo span { color: #FFD700; }
        .navbar nav a { color: #b2bec3; margin-left: 20px; text-decoration: none; font-size: 14px; font-weight: 500; }
        .navbar nav a:hover { color: #fff; }

        .main-container { display: flex; padding: 40px 8%; gap: 30px; max-width: 1400px; margin: 0 auto; }

        /* Filter Sidebar */
        .sidebar { 
            width: 300px; background: var(--white); padding: 30px; border-radius: 20px; 
            height: fit-content; box-shadow: 0 10px 30px rgba(0,0,0,0.04); border: 1px solid var(--border);
        }
        .sidebar h3 { margin-top: 0; font-size: 18px; font-weight: 700; margin-bottom: 25px; }

        /* Car Grid */
        .car-grid { flex: 1; display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; }
        .car-card { 
            background: var(--white); border-radius: 20px; overflow: hidden; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.03); transition: 0.3s; border: 1px solid var(--border);
        }
        .car-card:hover { transform: translateY(-10px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        .car-img-wrapper { position: relative; width: 100%; height: 200px; }
        .car-img { width: 100%; height: 100%; object-fit: cover; }
        
        .badge { position: absolute; top: 15px; left: 15px; padding: 5px 12px; border-radius: 8px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
        .badge-admin { background: #d63031; color: white; }
        .badge-status { background: rgba(255,255,255,0.9); color: #000; right: 15px; left: auto; }

        .car-info { padding: 25px; }
        .car-price { font-size: 22px; font-weight: 800; color: var(--primary); margin-bottom: 5px; }
        .car-title { font-size: 18px; font-weight: 700; margin-bottom: 10px; color: #1a1a1a; }
        .car-meta { font-size: 13px; color: #94a3b8; margin-bottom: 20px; display: flex; gap: 10px; }

        /* Buttons */
        .btn { display: block; text-align: center; padding: 12px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 700; transition: 0.2s; border: none; cursor: pointer; }
        .btn-view { background: var(--primary); color: #fff; margin-bottom: 10px; }
        .btn-view:hover { opacity: 0.9; }
        
        .btn-chat { background: #fff; color: var(--accent); border: 2px solid var(--accent); }
        .btn-chat:hover { background: var(--accent); color: #fff; }
        
        .btn-delete { background: #fff5f5; color: #e53e3e; font-size: 12px; margin-top: 15px; border: 1px dashed #feb2b2; }
        .btn-delete:hover { background: #e53e3e; color: #fff; }

        /* Form Styling */
        .filter-group { margin-bottom: 20px; }
        .filter-group label { display: block; font-size: 12px; font-weight: 700; margin-bottom: 8px; color: #64748b; text-transform: uppercase; }
        .filter-group select, .filter-group input { 
            width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: #fdfdfd; font-size: 14px;
        }
        .filter-btn { width: 100%; padding: 14px; background: var(--accent); color: #fff; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(9, 132, 227, 0.2); }

        @media (max-width: 900px) {
            .main-container { flex-direction: column; padding: 20px; }
            .sidebar { width: 100%; }
        }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">Phome Htet <span>Car Sales</span></a>
    <nav>
        <a href="index.php">Home</a>
        <a href="cars.php" style="color: #FFD700;">Inventory</a>
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin_dashboard.php">Admin Panel</a>
        <?php endif; ?>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="logout.php" style="color: #ff7675;">Logout</a>
        <?php else: ?>
            <a href="login.php" style="background: #FFD700; color: #000; padding: 8px 20px; border-radius: 12px; font-weight: 800;">Login</a>
        <?php endif; ?>
    </nav>
</header>

<div class="main-container">
    <aside class="sidebar">
        <h3>🔍 Filters</h3>
        <form action="cars.php" method="GET">
            <div class="filter-group">
                <label>Brand</label>
                <select name="brand">
                    <option value="">All Brands</option>
                    <?php 
                        $brands = ['Toyota', 'Honda', 'Suzuki', 'Mazda', 'Nissan', 'Mitsubishi'];
                        foreach($brands as $b): 
                    ?>
                        <option value="<?php echo strtolower($b); ?>" <?php if(@$_GET['brand']==strtolower($b)) echo 'selected'; ?>><?php echo $b; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Max Budget (MMK)</label>
                <input type="number" name="max_price" value="<?php echo htmlspecialchars(@$_GET['max_price']); ?>" placeholder="e.g. 50000000">
            </div>
            <button type="submit" class="filter-btn">Search Now</button>
            <a href="cars.php" style="display:block; text-align:center; font-size:12px; color:#94a3b8; margin-top:20px; text-decoration:none;">Clear all filters</a>
        </form>
    </aside>

    <main class="car-grid">
        <?php if ($cars): ?>
            <?php foreach ($cars as $car): ?>
                <div class="car-card">
                    <div class="car-img-wrapper">
                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <div class="badge badge-admin">ADMIN VIEW</div>
                        <?php endif; ?>
                        <div class="badge badge-status"><?php echo $car['status']; ?></div>
                        
                        <img src="<?php echo $car['image_url'] ? htmlspecialchars($car['image_url']) : 'https://via.placeholder.com/400x250'; ?>" class="car-img">
                    </div>
                    
                    <div class="car-info">
                        <div class="car-price"><?php echo number_format($car['price']); ?> <span style="font-size: 12px; color: #94a3b8;">MMK</span></div>
                        <div class="car-title"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></div>
                        <div class="car-meta">
                            <span>📅 <?php echo $car['year']; ?></span>
                            <span>•</span>
                            <span>📍 <?php echo number_format($car['mileage']); ?> km</span>
                        </div>
                        
                        <a href="car_details.php?id=<?php echo $car['id']; ?>" class="btn btn-view">View Details</a>

                        <?php 
                        // Logic: "Chat with Seller" ကို User (Buyer) ဖြစ်မှသာ ပြမည်
                        // Admin သို့မဟုတ် Seller ကိုယ်တိုင်ဖြစ်လျှင် မပြပါ
                        if(isset($_SESSION['user_id'])): 
                            if($_SESSION['role'] === 'user' && $_SESSION['user_id'] != $car['seller_id']): ?> 
                                <a href="car_chat.php?car_id=<?php echo (int)$car['id']; ?>&seller_id=<?php echo (int)$car['seller_id']; ?>" class="btn btn-chat">
                                    💬 Chat with Seller
                                </a>
                            <?php elseif($_SESSION['user_id'] == $car['seller_id']): ?>
                                <p style="text-align: center; font-size: 11px; color: #94a3b8; margin-top: 10px; font-weight: 600;">(Your Listing)</p>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-chat" style="border-color: #cbd5e1; color: #94a3b8;">
                                Login to Chat
                            </a>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="cars.php?delete_id=<?php echo $car['id']; ?>" class="btn btn-delete" onclick="return confirm('ဖျက်မှာ သေချာလား?')">Remove Listing</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 100px 20px;">
                <h2 style="color: #cbd5e1;">No cars found.</h2>
                <a href="cars.php" style="color: var(--accent); font-weight: 700; text-decoration: none;">View All Inventory</a>
            </div>
        <?php endif; ?>
    </main>
</div>

</body>
</html>