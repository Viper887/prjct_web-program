<?php
require 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    die("Доступ лише для продавців");
}

$stmt = $pdo->prepare("
    SELECT o.*, p.title as product_name, u.name as buyer_name 
    FROM orders o
    JOIN products p ON o.product_id = p.id
    JOIN users u ON o.buyer_id = u.id
    WHERE p.seller_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <link rel="stylesheet" href="style.css">
    <title>Мої замовлення</title>
</head>
<body>
    <h1>Замовлення на ваші товари</h1>
    <div class="products-container">
        <?php foreach ($orders as $order): ?>
            <div class="card">
                <p><strong>Товар:</strong> <?php echo $order['product_name']; ?></p>
                <p><strong>Покупець:</strong> <?php echo $order['buyer_name']; ?></p>
                <p><strong>Адреса:</strong> <?php echo $order['address']; ?></p>
                <p><strong>Дата:</strong> <?php echo $order['created_at']; ?></p>
                <p><strong>Статус:</strong> <span class="badge"><?php echo $order['status']; ?></span></p>
            </div>
        <?php endforeach; ?>
    </div>
    <a href="index.php">На головну</a>
</body>
</html>