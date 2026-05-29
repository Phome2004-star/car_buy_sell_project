<?php
require_once 'db.php';
$conn = db();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $price = $_POST['price'];
    $mileage = $_POST['mileage'];
    $engine_power = $_POST['engine_power'];
    $transmission = $_POST['transmission'];
    $description = $_POST['description'];
    $seller_id = $_SESSION['user_id'];

    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    $image = time() . "_" . $_FILES['image']['name'];
    $target = "uploads/" . basename($image);
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $sql = "INSERT INTO car_listings (brand, model, year, price, mileage, engine_power, transmission, description, image_url, seller_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Available')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$brand, $model, $year, $price, $mileage, $engine_power, $transmission, $description, $target, $seller_id]);
        echo "<script>alert('ကားတင်ပြီးပါပြီ'); window.location='seller_index.php';</script>";
    } else {
        $error = "ပုံတင်ရတာ အဆင်မပြေဖြစ်သွားပါတယ်။";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post New Car | Phome Htet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f8f9fa;
            --accent-blue: #0984e3;
            --text-dark: #2d3436;
            --white: #ffffff;
            --border-color: #edf2f7;
        }

        * { box-sizing: border-box; font-family: 'Inter', sans-serif; }
        
        body {
            margin: 0;
            background-color: var(--bg-color);
            color: var(--text-dark);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            background: #1a1c1e;
            padding: 15px 10%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }
        .logo { font-size: 20px; font-weight: 800; }
        .logo span { color: #FFD700; }
        .navbar a { color: #b2bec3; text-decoration: none; margin-left: 20px; font-size: 14px; font-weight: 500; }
        .navbar a:hover { color: white; }

        .main-content {
            flex: 1;
            padding: 50px 20px;
            display: flex;
            justify-content: center;
        }

        .form-container {
            background: var(--white);
            max-width: 750px;
            width: 100%;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .form-header { margin-bottom: 30px; text-align: center; }
        .form-header h2 { margin: 0; font-size: 24px; font-weight: 700; }
        .form-header p { color: #636e72; margin-top: 5px; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .input-group { margin-bottom: 20px; }
        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #636e72;
        }

        .input-group input, 
        .input-group select, 
        .input-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 14px;
            background: #fdfdfd;
            outline: none;
            transition: border-color 0.3s;
        }

        .input-group input:focus, .input-group select:focus {
            border-color: var(--accent-blue);
        }

        .file-input {
            border: 1px dashed #cbd5e0 !important;
            background: #f8fafc !important;
            cursor: pointer;
        }

        .submit-btn {
            background: var(--accent-blue);
            color: white;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(9, 132, 227, 0.2);
            margin-top: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(9, 132, 227, 0.3);
        }

        .error-msg {
            background: #fff5f5;
            color: #c53030;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        @media (max-width: 600px) {
            .form-grid { grid-template-columns: 1fr; }
            .form-container { padding: 25px; }
        }
    </style>
</head>
<body>

<header class="navbar">
    <div class="logo">Phome Htet <span>Seller</span></div>
    <nav>
        <a href="seller_index.php">Dashboard</a>
        <a href="index.php">View Site</a>
        <a href="logout.php" style="color: #ff7675;">Logout</a>
    </nav>
</header>

<main class="main-content">
    <div class="form-container">
        <div class="form-header">
            <h2>Post a New Listing</h2>
            <p>ရောင်းလိုသည့် ကားအချက်အလက်များကို အသေးစိတ် ဖြည့်စွက်ပါ</p>
        </div>
        
        <?php if(isset($error)) echo "<div class='error-msg'>$error</div>"; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="input-group">
                    <label>Car Brand</label>
                    <input type="text" name="brand" placeholder="e.g. Toyota" required>
                </div>
                <div class="input-group">
                    <label>Model</label>
                    <input type="text" name="model" placeholder="e.g. Crown" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="input-group">
                    <label>Year</label>
                    <input type="number" name="year" placeholder="2018" required>
                </div>
                <div class="input-group">
                    <label>Price (MMK)</label>
                    <input type="number" name="price" placeholder="85000000" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="input-group">
                    <label>Mileage (km)</label>
                    <input type="number" name="mileage" placeholder="45000" required>
                </div>
                <div class="input-group">
                    <label>Engine Power</label>
                    <input type="text" name="engine_power" placeholder="2500cc" required>
                </div>
            </div>

            <div class="input-group">
                <label>Transmission</label>
                <select name="transmission">
                    <option value="Auto">Automatic</option>
                    <option value="Manual">Manual</option>
                </select>
            </div>

            <div class="input-group">
                <label>Description</label>
                <textarea name="description" rows="4" placeholder="ကား၏ အခြေအနေ၊ ပါဝင်သည့် Feature များကို ဖော်ပြပါ..."></textarea>
            </div>

            <div class="input-group">
                <label>Car Photo</label>
                <input type="file" name="image" class="file-input" required>
            </div>
            
            <button type="submit" class="submit-btn">Publish Listing</button>
            <a href="seller_index.php" style="display:block; text-align:center; margin-top:20px; color:#a0aec0; text-decoration:none; font-size: 14px;">Back to Dashboard</a>
        </form>
    </div>
</main>

<footer style="background: #1a1c1e; color: #636e72; padding: 25px; text-align: center; font-size: 13px;">
    <p>&copy; <?php echo date("Y"); ?> Phome Htet Car Sales. Crafted for Sellers.</p>
</footer>

</body>
</html>