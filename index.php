<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Крафтові товари Полтави</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

    <h1>Крафтові товари Полтави</h1>

    <div class="user-menu">
        <?php if (isset($_SESSION['user_id'])): ?>
            <p>Вітаємо, <?php echo htmlspecialchars($_SESSION['name']); ?>! | <a href="logout.php">Вийти</a></p>
            <?php if ($_SESSION['role'] == 'seller'): ?>
                <a href="add_product.php">Додати товар</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="login.php">Увійти</a> | <a href="register.php">Реєстрація</a>
        <?php endif; ?>
    </div>

    <hr>

    <div style="display: flex; flex-wrap: wrap;">
        <?php
        $stmt = $pdo->query("SELECT * FROM products");
        while($row = $stmt->fetch()) {
            echo "<div class='card'>
                    <img src='{$row['image_path']}' width='150' alt='{$row['title']}'><br>
                    <h3>{$row['title']}</h3>
                    <p>{$row['price']} грн</p>
                    <a href='order.php?id={$row['id']}'>Купити</a>
                  </div>";
        }
        ?>
    </div>

</body>
</html>