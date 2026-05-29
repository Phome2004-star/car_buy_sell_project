<?php
require_once 'db.php';
$conn = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sold_out'])) {
    $car_id = (int)$_POST['car_id'];
    $seller_id = $_SESSION['user_id'];

    // ကိုယ်ပိုင်တဲ့ကား ဟုတ်မဟုတ် အရင်စစ်မယ်
    $stmt = $conn->prepare("UPDATE car_listings SET status = 'Sold Out' WHERE id = ? AND seller_id = ?");
    $stmt->execute([$car_id, $seller_id]);

    echo "<script>alert('ကားကို Sold Out အဖြစ် ပြောင်းလဲပြီးပါပြီ'); window.location='car_details.php?id=$car_id';</script>";
}
?>