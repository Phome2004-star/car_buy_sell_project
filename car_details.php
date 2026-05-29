<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$conn = db();

$car_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current_user_id = $_SESSION['user_id'] ?? null; 

try {
    // Database Table ပေါ်မူတည်ပြီး user_id သို့မဟုတ် seller_id ကို ညှိပေးပါ
    $stmt = $conn->prepare("
        SELECT c.*, u.username as seller_name, u.email as seller_email 
        FROM car_listings c 
        LEFT JOIN users u ON c.seller_id = u.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

if (!$car) {
    die("Car not found!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_sold'])) {
    if ($current_user_id == $car['seller_id']) {
        $update = $conn->prepare("UPDATE car_listings SET status = 'Sold Out' WHERE id = ?");
        $update->execute([$car_id]);
        header("Location: car_details.php?id=" . $car_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?> | Phome Htet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a1c1e;
            --accent: #0984e3;
            --bg: #f8f9fa;
            --white: #ffffff;
            --text-main: #2d3436;
            --text-muted: #636e72;
            --success: #00b894;
            --danger: #d63031;
        }

        * { box-sizing: border-box; transition: all 0.3s ease; }
        body { background: var(--bg); color: var(--text-main); font-family: 'Inter', sans-serif; margin: 0; line-height: 1.6; }

        /* Navbar */
        .navbar { background: var(--primary); padding: 18px 8%; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo { color: white; font-size: 22px; font-weight: 800; text-decoration: none; }
        .logo span { color: #FFD700; }
        .nav-links a { color: #b2bec3; text-decoration: none; margin-left: 25px; font-size: 14px; font-weight: 500; }
        .nav-links a:hover { color: white; }

        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 1.6fr 1fr; gap: 40px; }

        /* Left Side: Image & Content */
        .main-visual { width: 100%; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.08); background: #eee; }
        .car-hero-img { width: 100%; height: 550px; object-fit: cover; display: block; }
        
        .description-card { background: var(--white); padding: 40px; border-radius: 24px; margin-top: 30px; border: 1px solid #edf2f7; }
        .description-card h2 { margin-top: 0; font-size: 22px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .description-card p { color: var(--text-muted); font-size: 16px; white-space: pre-line; }

        /* Right Side: Sticky Info Box */
        .info-sidebar { position: sticky; top: 100px; height: fit-content; }
        .info-card { background: var(--white); padding: 35px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); border: 1px solid #edf2f7; }

        .status-pill { display: inline-block; padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; }
        .status-available { background: #e6fffa; color: var(--success); }
        .status-sold { background: #fff5f5; color: var(--danger); }

        .car-title { font-size: 32px; font-weight: 800; margin: 0 0 8px 0; color: var(--primary); letter-spacing: -1px; }
        .car-meta { color: var(--text-muted); font-size: 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px; }

        .price-display { font-size: 36px; font-weight: 800; color: var(--primary); margin-bottom: 30px; display: flex; align-items: baseline; gap: 8px; }
        .price-display span { font-size: 16px; color: var(--text-muted); font-weight: 500; }

        .spec-list { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 35px; padding: 25px 0; border-top: 1px solid #f1f2f6; border-bottom: 1px solid #f1f2f6; }
        .spec-item label { display: block; font-size: 11px; text-transform: uppercase; color: #a0aec0; font-weight: 700; margin-bottom: 4px; }
        .spec-item span { display: block; font-size: 15px; font-weight: 600; color: var(--text-main); }

        /* Buttons */
        .btn { width: 100%; padding: 16px; border-radius: 14px; border: none; font-weight: 700; cursor: pointer; text-align: center; text-decoration: none; font-size: 15px; display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 12px; }
        .btn-primary { background: var(--primary); color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .btn-primary:hover { background: #333; transform: translateY(-2px); }
        .btn-outline { background: transparent; border: 2px solid #e2e8f0; color: var(--text-main); }
        .btn-outline:hover { background: #f8f9fa; border-color: var(--text-main); }
        
        .seller-actions { background: #fffaf0; padding: 20px; border-radius: 16px; border: 1px solid #feebc8; margin-bottom: 20px; }
        .btn-danger { background: var(--danger); color: white; }

        @media (max-width: 992px) {
            .container { grid-template-columns: 1fr; }
            .car-hero-img { height: 400px; }
            .info-sidebar { position: static; }
        }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">Phome Htet <span>Car Sales</span></a>
    <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="cars.php">Inventory</a>
        <a href="service.php">Services</a>
    </nav>
</header>

<div class="container">
    <!-- Left Column -->
    <main>
        <div class="main-visual">
            <img src="<?php echo htmlspecialchars($car['image_url']); ?>" class="car-hero-img" alt="Car Main View">
        </div>

        <div class="description-card">
            <h2>📜 Detailed Description</h2>
            <p><?php echo nl2br(htmlspecialchars($car['description'])); ?></p>
        </div>
    </main>

    <!-- Right Column -->
    <aside class="info-sidebar">
        <div class="info-card">
            <span class="status-pill <?php echo ($car['status'] == 'Sold Out') ? 'status-sold' : 'status-available'; ?>">
                <?php echo strtoupper($car['status']); ?>
            </span>

            <h1 class="car-title"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h1>
            <div class="car-meta">
                <span>📅 <?php echo $car['year']; ?> Model</span>
                <span>👤 <?php echo htmlspecialchars($car['seller_name']); ?></span>
            </div>

            <div class="price-display">
                <?php echo number_format($car['price']); ?> <span>MMK</span>
            </div>

            <div class="spec-list">
                <div class="spec-item">
                    <label>Mileage</label>
                    <span><?php echo number_format($car['mileage']); ?> km</span>
                </div>
                <div class="spec-item">
                    <label>Engine</label>
                    <span><?php echo htmlspecialchars($car['engine_power']); ?></span>
                </div>
                <div class="spec-item">
                    <label>Transmission</label>
                    <span><?php echo htmlspecialchars($car['transmission']); ?></span>
                </div>
                <div class="spec-item">
                    <label>Fuel Type</label>
                    <span>Petrol/Hybrid</span>
                </div>
            </div>

            <div class="action-stack">
                <!-- Seller Management Tools -->
                <?php if ($current_user_id == $car['seller_id']): ?>
                    <div class="seller-actions">
                        <p style="margin: 0 0 12px 0; font-size: 13px; font-weight: 700; color: #7b341e;">Manage Your Listing</p>
                        <a href="edit_car.php?id=<?php echo $car['id']; ?>" class="btn btn-outline">✏️ Edit Details</a>
                        <?php if ($car['status'] !== 'Sold Out'): ?>
                            <form method="POST">
                                <button type="submit" name="mark_sold" class="btn btn-danger" onclick="return confirm('ဒီကားကို Sold Out အဖြစ် သတ်မှတ်မှာ သေချာပါသလား?')">✅ Mark as Sold Out</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Buyer Options -->
                <?php if ($current_user_id != $car['seller_id']): ?>
                    <a href="mailto:<?php echo $car['seller_email']; ?>" class="btn btn-primary">✉️ Send Email Inquiry</a>
                    
                    <?php if ($current_user_id): ?>
                        <a href="car_chat.php?car_id=<?php echo $car['id']; ?>&seller_id=<?php echo $car['seller_id']; ?>" class="btn btn-outline">💬 Message Seller</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">🔒 Login to Chat</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </aside>
</div>

<footer style="background: var(--primary); padding: 60px 20px; text-align: center; color: #636e72; border-top: 1px solid #2d3436;">
    <p style="color: white; font-weight: 700; margin-bottom: 10px;">Phome Htet Car Sales</p>
    <p style="font-size: 13px;">© <?php echo date("Y"); ?> All rights reserved. Premium Automotive Marketplace.</p>
</footer>

</body>
</html>