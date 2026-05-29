<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Login မဝင်ထားရင် Login page ကို ပို့မယ်
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=booking.php");
    exit();
}

$conn = db();
$user_id = $_SESSION['user_id'];
$pre_selected_service = isset($_GET['service']) ? $_GET['service'] : '';

// Form Submit လုပ်တဲ့ Logic
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_type = $_POST['service_type'];
    $booking_date = $_POST['booking_date'];
    $car_model = $_POST['car_model'];
    $note = $_POST['note'];

    try {
        $stmt = $conn->prepare("INSERT INTO service_bookings (user_id, service_type, booking_date, car_model, note, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$user_id, $service_type, $booking_date, $car_model, $note]);
        $msg = "success";
    } catch (PDOException $e) {
        $msg = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Service | Phome Htet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #000; --accent: #FFD700; --bg: #f4f7f6; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; padding: 0; }
        
        .navbar { background: var(--primary); padding: 15px 8%; display: flex; justify-content: space-between; align-items: center; }
        .navbar .logo { color: #fff; font-size: 22px; font-weight: bold; text-decoration: none; }
        .navbar .logo span { color: var(--accent); }

        .booking-container { max-width: 600px; margin: 50px auto; background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; font-weight: 700; color: #333; text-align: center; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #555; }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; font-family: inherit; font-size: 15px; box-sizing: border-box;
        }
        
        .submit-btn { 
            width: 100%; padding: 15px; background: var(--primary); color: #fff; border: none; 
            border-radius: 10px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 16px;
        }
        .submit-btn:hover { background: #333; transform: translateY(-2px); }

        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-size: 14px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">Phome Htet <span>Car Sales</span></a>
</header>

<div class="booking-container">
    <h2>Book Your Service</h2>
    <p style="text-align: center; color: #888; font-size: 14px; margin-bottom: 30px;">Fill out the form below to schedule your visit.</p>

    <?php if($msg === "success"): ?>
        <div class="alert alert-success">✅ Booking successful! We will contact you soon.</div>
    <?php elseif($msg === "error"): ?>
        <div class="alert alert-error">❌ Something went wrong. Please try again.</div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Service Type</label>
            <select name="service_type" required>
                <option value="">Select a service</option>
                <option value="Engine Repair" <?php if($pre_selected_service == 'engine') echo 'selected'; ?>>Engine Repair</option>
                <option value="Oil Change" <?php if($pre_selected_service == 'oil') echo 'selected'; ?>>Oil Change</option>
                <option value="Brake System" <?php if($pre_selected_service == 'brake') echo 'selected'; ?>>Brake System</option>
                <option value="Premium Wash" <?php if($pre_selected_service == 'wash') echo 'selected'; ?>>Premium Wash</option>
                <option value="Battery Check" <?php if($pre_selected_service == 'battery') echo 'selected'; ?>>Battery Check</option>
                <option value="Air Conditioning" <?php if($pre_selected_service == 'ac') echo 'selected'; ?>>Air Conditioning</option>
            </select>
        </div>

        <div class="form-group">
            <label>Preferred Date</label>
            <input type="date" name="booking_date" min="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div class="form-group">
            <label>Your Car Model</label>
            <input type="text" name="car_model" placeholder="e.g. Toyota Crown 2018" required>
        </div>

        <div class="form-group">
            <label>Additional Notes (Optional)</label>
            <textarea name="note" rows="4" placeholder="Tell us more about your car's issue..."></textarea>
        </div>

        <button type="submit" class="submit-btn">Confirm Booking</button>
        <a href="service.php" style="display:block; text-align:center; margin-top:15px; color:#999; text-decoration:none; font-size:13px;">Cancel</a>
    </form>
</div>

</body>
</html>