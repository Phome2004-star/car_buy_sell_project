<?php 
require_once 'db.php'; 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Services | Phome Htet</title>
    <style>
        /* Base Styles */
        body { background: #f4f7f6; color: #333; font-family: 'Segoe UI', Tahoma, sans-serif; margin: 0; }
        
        /* Navbar Styling */
        .navbar { 
            background: #000; padding: 15px 8%; display: flex; 
            justify-content: space-between; align-items: center; 
            position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar .logo { color: #fff; font-size: 22px; font-weight: bold; text-decoration: none; }
        .navbar .logo span { color: #FFD700; }
        .navbar nav a { color: #ccc; margin-left: 20px; text-decoration: none; font-size: 14px; transition: 0.3s; }
        .navbar nav a:hover { color: #fff; }
        .login-btn { background: #FFD700; color: #000; border: none; padding: 6px 18px; border-radius: 50px; font-weight: bold; cursor: pointer; }

        /* Header Section */
        .service-header {
            text-align: center;
            padding: 100px 20px;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?auto=format&fit=crop&w=1500&q=80');
            background-size: cover;
            background-position: center;
            color: #FFF;
        }
        .service-header h1 { font-size: 48px; margin-bottom: 10px; }
        .service-header p { opacity: 0.9; font-size: 18px; color: #FFD700; }

        /* Service Grid */
        .services-container { padding: 80px 8%; }
        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .service-card {
            background: #FFF;
            padding: 40px 30px;
            border-radius: 20px;
            text-align: center;
            transition: 0.4s;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border-bottom: 4px solid transparent;
            /* ခလုတ်ကို အောက်ဆုံးပို့ဖို့ flex သုံးထားတယ် */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 350px; 
        }
        .service-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            border-bottom: 4px solid #FFD700;
        }

        .service-icon { font-size: 50px; margin-bottom: 20px; display: block; }
        .service-card h3 { font-size: 22px; margin-bottom: 15px; color: #000; }
        .service-card p { color: #636e72; font-size: 15px; line-height: 1.6; margin-bottom: 20px; flex-grow: 1; }

        /* Book Appointment Button */
        .book-btn {
            background: #000;
            color: #FFD700;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: bold;
            transition: 0.3s;
            display: block;
            margin-top: auto; /* Push to bottom */
        }
        .book-btn:hover {
            background: #FFD700;
            color: #000;
        }

        /* Fan Section & Footer stays same... */
        .fan-section { background: #000; color: #FFF; padding: 80px 5%; text-align: center; border-radius: 30px; margin: 50px 8% 80px; position: relative; overflow: hidden; }
        .fan-badge { background: #FFD700; color: #000; padding: 6px 20px; border-radius: 50px; font-size: 12px; text-transform: uppercase; font-weight: bold; display: inline-block; margin-bottom: 25px; }
        .join-btn { background: #FFF; color: #000; border: none; padding: 16px 45px; border-radius: 50px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 16px; }
        .join-btn:hover { background: #FFD700; transform: scale(1.05); }
        footer { background: #000; color: #666; padding: 40px 8%; text-align: center; border-top: 1px solid #222; }

        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 15px; }
            .service-header h1 { font-size: 32px; }
        }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">Phome Htet <span>Car Sales</span></a>
    <nav>
        <a href="index.php">Home</a>
        <a href="cars.php">Cars</a>
        <a href="service.php" style="color: #FFD700;">Services</a>
        <a href="customerservice.php">Support</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="logout.php" style="color: #ff7675;">Logout</a>
        <?php else: ?>
            <button class="login-btn" onclick="window.location='login.php'">Login</button>
        <?php endif; ?>
    </nav>
</header>

<section class="service-header">
    <h1>Our Professional Services</h1>
    <p>Keeping your dream car in perfect condition</p>
</section>

<section class="services-container">
    <div class="service-grid">
        <div class="service-card">
            <span class="service-icon">🛠️</span>
            <h3>Engine Repair</h3>
            <p>Expert diagnostics and engine rebuilding services using genuine parts to ensure your car's longevity.</p>
            <a href="booking.php?service=engine" class="book-btn">Book Appointment</a>
        </div>

        <div class="service-card">
            <span class="service-icon">🛢️</span>
            <h3>Oil Change</h3>
            <p>Premium synthetic oil change with a complimentary 20-point safety inspection for every visit.</p>
            <a href="booking.php?service=oil" class="book-btn">Book Appointment</a>
        </div>

        <div class="service-card">
            <span class="service-icon">🛑</span>
            <h3>Brake System</h3>
            <p>Complete brake pad replacement, rotor resurfacing, and fluid check for your absolute safety on the road.</p>
            <a href="booking.php?service=brake" class="book-btn">Book Appointment</a>
        </div>

        <div class="service-card">
            <span class="service-icon">🧽</span>
            <h3>Premium Wash</h3>
            <p>Deep interior cleaning and exterior detailing with ceramic coating options to make your car shine like new.</p>
            <a href="booking.php?service=wash" class="book-btn">Book Appointment</a>
        </div>

        <div class="service-card">
            <span class="service-icon">🔋</span>
            <h3>Battery Check</h3>
            <p>Fast battery health testing and high-quality replacement with warranty for all vehicle models.</p>
            <a href="booking.php?service=battery" class="book-btn">Book Appointment</a>
        </div>

        <div class="service-card">
            <span class="service-icon">❄️</span>
            <h3>Air Conditioning</h3>
            <p>Full AC gas refill, leak detection, and cooling system repair for ultimate cabin comfort in any weather.</p>
            <a href="booking.php?service=ac" class="book-btn">Book Appointment</a>
        </div>
    </div>
</section>

<section class="fan-section">
    <span class="fan-badge">Exclusive for Owners</span>
    <h2 style="font-size: 36px; margin-bottom: 20px;">Car Lovers Club & Customization</h2>
    <p style="max-width: 700px; margin: 0 auto 40px; opacity: 0.8; line-height: 1.8;">
        ကျွန်တော်တို့ Phome Htet Car Sales မှ ကားဝယ်ယူထားသူများအတွက် Performance Tuning, 
        Body Kit Installation နှင့် Custom Modification ဝန်ဆောင်မှုများကို အထူး Discount များဖြင့် 
        ဆောင်ရွက်ပေးနေပါပြီ။
    </p>
    <button class="join-btn" onclick="window.location='customerservice.php'">Join the Club Now</button>
</section>

<footer>
    <p style="color: #fff; margin-bottom: 10px;">Phome Htet Car Sales</p>
    <p>&copy; <?php echo date("Y"); ?> All rights reserved.</p>
</footer>

</body>
</html>