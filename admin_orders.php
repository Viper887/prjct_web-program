<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    die("Доступ лише для продавців");
}

$seller_id = $_SESSION['user_id'];

// 1. Отримуємо всі замовлення з бази даних
// Оскільки товари тепер у JSON, ми беремо всі замовлення, а потім відфільтруємо ті, 
// де є товари поточного продавця, або просто виведемо деталі замовлень.
$stmt = $pdo->prepare("
    SELECT o.*, u.name as buyer_name 
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
$stmt->execute();
$all_orders = $stmt->fetchAll();

// 2. Фільтруємо замовлення, щоб показувати продавцю лише його товари
$seller_orders = [];

foreach ($all_orders as $order) {
    $items = json_decode($order['items_json'], true);
    $seller_items = [];
    $seller_subtotal = 0;

    if (is_array($items)) {
        foreach ($items as $item) {
            // Робимо швидкий запит до БД, щоб дізнатися, чи належить цей товар поточному продавцю
            $stmt_prod = $pdo->prepare("SELECT seller_id FROM products WHERE id = ?");
            $stmt_prod->execute([$item['product_id']]);
            $product = $stmt_prod->fetch();

            if ($product && $product['seller_id'] == $seller_id) {
                $seller_items[] = $item;
                $seller_subtotal += $item['price'] * $item['quantity'];
            }
        }
    }

    // Якщо у цьому замовленні є хоча б один товар цього продавця, додаємо його до списку відображення
    if (!empty($seller_items)) {
        $order['my_items'] = $seller_items;
        $order['my_total'] = $seller_subtotal;
        $seller_orders[] = $order;
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель замовлень продавця</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .orders-container {
            max-width: 1000px;
            width: 95%;
            margin: 40px auto;
        }
        .order-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(163, 29, 29, 0.2);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid rgba(163, 29, 29, 0.1);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .order-status {
            font-weight: bold;
            color: #a31d1d;
            text-transform: uppercase;
        }
        .buyer-info {
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
        }
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .order-items-table th, .order-items-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>

    <div class="header-logo">
        <h1>Панель продавця</h1>
        <div style="margin-top: 10px;">
            <a href="index.php" style="color: #fff; text-decoration: none; font-weight: bold;">← На головну</a>
        </div>
    </div>

    <div class="orders-container">
        <h2 style="margin-bottom: 25px; text-align: center;">Отримані замовлення</h2>

        <?php if (empty($seller_orders)): ?>
            <p style="text-align: center; color: #666; font-size: 18px;">У вас ще немає замовлень.</p>
        <?php else: ?>
            <?php foreach ($seller_orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <h3>Замовлення №<?php echo $order['id']; ?></h3>
                        <span class="order-status"><?php echo htmlspecialchars($order['status']); ?></span>
                    </div>
                    
                    <div class="buyer-info">
                        <strong>Покупець:</strong> <?php echo htmlspecialchars($order['customer_name']); ?> (<?php echo htmlspecialchars($order['buyer_name'] ?? 'Гість'); ?>)<br>
                        <strong>Телефон:</strong> <?php echo htmlspecialchars($order['phone']); ?><br>
                        <strong>Адреса доставки:</strong> <?php echo htmlspecialchars($order['address']); ?><br>
                        <strong>Дата:</strong> <?php echo $order['created_at']; ?>
                    </div>

                    <h4>Товари від вашого магазину у цьому замовленні:</h4>
                    <table class="order-items-table">
                        <thead>
                            <tr>
                                <th>Назва товару</th>
                                <th>Ціна</th>
                                <th>Кількість</th>
                                <th>Сума</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['my_items'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                                    <td><?php echo $item['price']; ?> грн</td>
                                    <td><?php echo $item['quantity']; ?> шт.</td>
                                    <td><?php echo $item['price'] * $item['quantity']; ?> грн</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div style="text-align: right; margin-top: 15px; font-size: 16px; font-weight: bold;">
                        Сума до сплати вам: <span style="color: #a31d1d; font-size: 18px;"><?php echo $order['my_total']; ?> грн</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</body>
</html>