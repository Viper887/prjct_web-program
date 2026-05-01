<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Крафт Бокс — Крафтові товари Полтави</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="header-logo">
        <h1>Kraft Box</h1>
    </div>

    <div class="user-menu">
        <?php if (isset($_SESSION['user_id'])): ?>
            <p>Вітаємо, <?php echo htmlspecialchars($_SESSION['name']); ?>! | <a href="logout.php">Вийти</a></p>
            <?php if ($_SESSION['role'] == 'seller'): ?>
                <a href="add_product.php">Додати товар</a> | 
                <a href="admin_orders.php"><b>Переглянути замовлення</b></a>
            <?php endif; ?>
        <?php else: ?>
            <a href="login.php">Увійти</a> | <a href="register.php">Реєстрація</a>
        <?php endif; ?>
    </div>

<!-- Оновлений блок Hero -->
<div class="hero-banner">
    <img src="uploads/own.png" alt="Kraft Box Banner">
</div>
    </div>

    <h2 class="catalog-title">Каталог</h2>

    <div class="products-container">
        <?php
        $stmt = $pdo->query("SELECT * FROM products");
        while($row = $stmt->fetch()) {
            echo "<div class='card'>
                    <img src='{$row['image_path']}' alt='{$row['title']}'>
                    <h3>{$row['title']}</h3>
                    <p class='price'>{$row['price']} грн</p>
                    <a href='order.php?id={$row['id']}' class='buy-btn'>Купити</a>
                  </div>";
        }
        ?>
    </div>

</body>
</html>