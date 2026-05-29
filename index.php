<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$conn = db();

try {
    $stmt = $conn->query("SELECT * FROM car_listings ORDER BY id DESC LIMIT 3");
    $recent_cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_cars = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phome Htet | Car Sales Service</title>
    <style>
        /* Base Styles */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background: #f9f9f9; color: #333; }
        a { text-decoration: none; transition: 0.3s; }

        /* Navbar Custom UI */
        .navbar { 
            background: #000; padding: 15px 8%; display: flex; 
            justify-content: space-between; align-items: center; 
            position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .logo { color: #fff; font-size: 22px; font-weight: bold; }
        .logo span { color: #FFD700; }
        .nav-right { display: flex; align-items: center; gap: 20px; }
        .nav-right a { color: #ccc; font-size: 15px; font-weight: 500; }
        .nav-right a:hover { color: #fff; }
        .user-greet { color: #eee; font-size: 14px; border-left: 1px solid #444; padding-left: 15px; }

        /* Hero Section */
        .hero { 
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1500&q=80');
            background-size: cover; background-position: center;
            height: 500px; display: flex; align-items: center; justify-content: center; text-align: center; color: #fff;
        }
        .hero-content h1 { font-size: 48px; margin-bottom: 10px; }
        .search-box { 
            background: #fff; padding: 10px; border-radius: 50px; 
            display: flex; gap: 10px; margin-top: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .search-box select, .search-box input { border: none; padding: 12px 20px; outline: none; font-size: 15px; }
        .search-box button { 
            background: #000; color: #fff; border: none; padding: 12px 30px; 
            border-radius: 50px; cursor: pointer; font-weight: bold; 
        }

        /* Car Grid UI */
        .car-grid { 
            display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            gap: 30px; padding: 40px 0; 
        }
        .car-card { 
            background: #fff; border-radius: 12px; overflow: hidden; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.08); transition: 0.3s; 
        }
        .car-card:hover { transform: translateY(-10px); }
        .badge { padding: 5px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-available { background: #d4edda; color: #155724; }
        .badge-sold { background: #f8d7da; color: #721c24; }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 15px; text-align: center; }
            .hero-content h1 { font-size: 32px; }
            .search-box { flex-direction: column; border-radius: 15px; }
        }
    </style>
</head>
<body>

<header class="navbar">
    <div class="logo">Phome Htet <span>Car Sales</span></div>
    <nav class="nav-right">
        <a href="cars.php">Cars</a>
        <a href="service.php">Services</a>
        <a href="customerservice.php">Support</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin_dashboard.php" style="color: #FFD700;">📊 Admin Dashboard</a>
            <?php elseif ($_SESSION['role'] === 'seller'): ?>
                <a href="seller_index.php" style="color: #FFD700;">📊 Dashboard</a>
                <a href="add_car.php" style="background: #31A24C; color: #fff; padding: 6px 12px; border-radius: 6px;">+ Post Car</a>
            <?php endif; ?>
            <span class="user-greet">Hi, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b></span>
            <a href="logout.php" style="color: #ff4d4d;">Logout</a>
        <?php else: ?>
            <a href="login.php" style="background: #FFD700; color: #000; padding: 8px 20px; border-radius: 50px; font-weight: bold;">Login</a>
        <?php endif; ?>
    </nav>
</header>

<section class="hero">
    <div class="hero-content">
        <h1>Find Your Next Car</h1>
        <p>Simple, fast and reliable car deals in Myanmar.</p>
        
        <form action="cars.php" method="GET" class="search-box">
            <select name="brand">
                <option value="">All Brands</option>
                <option value="toyota">Toyota</option>
                <option value="honda">Honda</option>
                <option value="suzuki">Suzuki</option>
            </select>
            <input type="number" name="max_price" placeholder="Max Price (MMK)">
            <button type="submit">Search Now</button>
        </form>
    </div>
</section>

<section style="padding: 60px 8%;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 30px; margin: 0;">Featured Listings</h2>
            <div style="width: 50px; height: 4px; background: #FFD700; margin-top: 10px;"></div>
        </div>
        <a href="cars.php" style="color: #000; font-weight: bold;">View All Cars →</a>
    </div>

    <div class="car-grid">
        <?php if (!empty($recent_cars)): ?>
            <?php foreach ($recent_cars as $car): ?>
                <div class="car-card">
                    <div style="position: relative;">
                        <img src="<?php echo !empty($car['image_url']) ? htmlspecialchars($car['image_url']) : 'https://via.placeholder.com/400x250?text=No+Image'; ?>" 
                             style="width: 100%; height: 220px; object-fit: cover;" alt="Car">
                        
                        <span class="badge <?php echo ($car['status'] == 'Sold Out') ? 'badge-sold' : 'badge-available'; ?>" 
                              style="position: absolute; top: 15px; right: 15px;">
                            <?php echo strtoupper($car['status']); ?>
                        </span>
                    </div>

                    <div style="padding: 20px;">
                        <h3 style="margin: 0 0 10px 0; font-size: 19px;">
                            <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>
                        </h3>
                        <p style="color: #777; font-size: 14px; margin-bottom: 20px;">
                            📅 <?php echo htmlspecialchars($car['year']); ?> • 📍 <?php echo number_format($car['mileage']); ?> km
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 15px;">
                            <span style="font-size: 18px; font-weight: bold; color: #000;">
                                <?php echo number_format($car['price']); ?> <small style="font-size: 11px;">MMK</small>
                            </span>
                            <a href="car_details.php?id=<?php echo $car['id']; ?>" 
                               style="background: #000; color: #fff; padding: 8px 16px; border-radius: 6px; font-size: 13px;">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; grid-column: 1/-1; color: #999;">No cars available at the moment.</p>
        <?php endif; ?>
    </div>
</section>

<footer style="background: #000; color: #666; padding: 40px 8%; text-align: center; border-top: 1px solid #222;">
    <p style="color: #fff; margin-bottom: 10px;">Phome Htet Car Sales</p>
    <p>&copy; <?php echo date("Y"); ?> All rights reserved.</p>
</footer>

</body>
</html>