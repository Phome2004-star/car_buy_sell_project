<?php
require_once 'db.php';
$conn = db();

$car_id = (int)$_GET['id'];
$seller_id = $_SESSION['user_id'];

// ကားအချက်အလက် ဆွဲထုတ်မယ်
$stmt = $conn->prepare("SELECT * FROM car_listings WHERE id = ? AND seller_id = ?");
$stmt->execute([$car_id, $seller_id]);
$car = $stmt->fetch();

if (!$car) die("Access Denied!");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Post တင်တဲ့ Logic (Update SQL သုံးရပါမယ်)
    $brand = $_POST['brand'];
    $price = $_POST['price'];
    // ... ကျန်တဲ့ field တွေယူ ...

    $sql = "UPDATE car_listings SET brand=?, price=? WHERE id=? AND seller_id=?";
    $conn->prepare($sql)->execute([$brand, $price, $car_id, $seller_id]);
    
    header("Location: car_details.php?id=$car_id");
}
?>

<form method="POST">
    <input type="text" name="brand" value="<?php echo htmlspecialchars($car['brand']); ?>">
    <input type="number" name="price" value="<?php echo $car['price']; ?>">
    <button type="submit">Save Changes</button>
</form>