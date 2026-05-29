<?php
require_once 'db.php';
// session_start() ပါပြီးသား db.php ကို သုံးပါ
$conn = db();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

// ဒီ seller တင်ထားတဲ့ ကားတွေကိုပဲ ဆွဲထုတ်မယ်
$stmt = $conn->prepare("SELECT * FROM car_listings WHERE seller_id = ? ORDER BY created_at DESC");
$stmt->execute([$seller_id]);
$my_cars = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Listings | Phome Htet</title>
    <link rel="stylesheet" href="style.css?v=2.0">
    <style>
        body { background: #f6f6f6; font-family: 'Inter', sans-serif; }
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .listing-grid { display: grid; grid-template-columns: 1fr; gap: 20px; }
        .car-card { 
            background: #fff; padding: 20px; border-radius: 15px; 
            display: flex; gap: 20px; align-items: center; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #eee;
        }
        .car-img { width: 150px; height: 100px; object-fit: cover; border-radius: 10px; }
        .car-info { flex: 1; }
        .status-badge { 
            display: inline-block; padding: 4px 10px; border-radius: 20px; 
            font-size: 12px; font-weight: bold; margin-bottom: 5px;
        }
        .status-available { background: #e8f5e9; color: #2e7d32; }
        .status-sold { background: #ffebee; color: #c62828; }
        .action-links a { 
            text-decoration: none; font-size: 14px; font-weight: bold; 
            margin-right: 15px; color: #000; border-bottom: 1px solid #000;
        }
        .add-new-btn { 
            background: #000; color: #fff; padding: 12px 25px; 
            border-radius: 8px; text-decoration: none; font-weight: bold;
        }
        @media (max-width: 600px) {
            .car-card { flex-direction: column; align-items: flex-start; }
            .car-img { width: 100%; height: 180px; }
        }
    </style>
</head>
<body>

<header class="navbar">
    <div class="logo">Phome Htet <span>Seller</span></div>
    <nav>
        <a href="index.php">Home</a>
        <a href="my_listings.php" class="active">My Cars</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <div class="header-flex">
        <h2>My Car Listings</h2>
        <a href="add_car.php" class="add-new-btn">+ Post New Car</a>
    </div>

    <div class="listing-grid">
        <?php if (count($my_cars) > 0): ?>
            <?php foreach ($my_cars as $car): ?>
                <div class="car-card">
                    <img src="<?php echo htmlspecialchars($car['image_url']); ?>" class="car-img">
                    <div class="car-info">
                        <span class="status-badge <?php echo ($car['status'] == 'Sold Out') ? 'status-sold' : 'status-available'; ?>">
                            <?php echo strtoupper($car['status']); ?>
                        </span>
                        <h3 style="margin: 5px 0;"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h3>
                        <p style="color: #666; margin: 0; font-size: 14px;">Price: <?php echo number_format($car['price']); ?> MMK</p>
                    </div>
                    <div class="action-links">
                        <a href="car_details.php?id=<?php echo $car['id']; ?>">View Details</a>
                        <a href="edit_car.php?id=<?php echo $car['id']; ?>">Edit</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 50px; background: #fff; border-radius: 15px;">
                <p>သင်တင်ထားသောကား မရှိသေးပါ။</p>
                <a href="add_car.php" style="color: #000; font-weight: bold;">ကား စတင်တင်ရန် နှိပ်ပါ</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>