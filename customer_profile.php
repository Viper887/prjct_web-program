<?php
require 'config.php';

// Перевірка, чи користувач увійшов
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];



// Отримуємо дані користувача
$stmt_user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();

// Отримуємо історію замовлень покупця
// Примітка: таблиця orders повинна мати колонку user_id
$stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt_orders->execute([$user_id]);
$orders = $stmt_orders->fetchAll();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мій профіль — Craft Box</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Використовуємо ваші стилі для карток та профілю */
        .profile-header {
            text-align: center;
            padding: 40px 20px;
            background: #f9f7f5;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
        }

        .profile-info h2 { font-size: 28px; color: #333; }
        .profile-info p { color: #666; margin-top: 5px; }

        .orders-section {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .order-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: 0.3s;
        }

        .order-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.05); }

        .order-details b { color: #a11e1e; }
        .order-status {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: bold;
            background: #ececec;
        }

        .status-completed { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #a11e1e;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="header-logo">
        <a href="index.php" style="text-decoration: none; color: inherit;"><h1>Craft Box</h1></a>
    </div>

    <div class="profile-header">
        <div class="profile-info">
            <h2>Особистий кабінет покупця</h2>
            <p>Вітаємо, <b><?php echo htmlspecialchars($user['name']); ?></b>!</p>
            <p>Електронна пошта: <?php echo htmlspecialchars($user['email']); ?></p>
            <a href="index.php" class="back-link">← На головну до покупок</a>
        </div>
    </div>

    <div class="orders-section">
        <h3 class="catalog-title" style="text-align: left; margin-bottom: 20px;">Мої замовлення</h3>

        <?php if ($orders): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-details">
                        <p>Замовлення <b>#<?php echo $order['id']; ?></b></p>
                        <p style="font-size: 14px; color: #777;">Дата: <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></p>
                        <p>Сума: <b><?php echo number_format($order['total_price'], 2, '.', ''); ?> грн</b></p>
                    </div>
                    <div>
                        <span class="order-status <?php echo ($order['status'] == 'Виконано') ? 'status-completed' : 'status-pending'; ?>">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 50px; color: #999;">
                <p>Ви ще не зробили жодного замовлення.</p>
                <a href="index.php" class="buy-btn" style="display: inline-block; margin-top: 15px; text-decoration: none;">Перейти до каталогу</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>