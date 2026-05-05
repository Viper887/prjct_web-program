<?php 
require 'config.php'; 

// --- ЛОГІКА КОШИКА ---
if (isset($_GET['add_to_cart'])) {
    $product_id = (int)$_GET['add_to_cart'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]++;
    } else {
        $_SESSION['cart'][$product_id] = 1;
    }
    header("Location: index.php");
    exit;
}

if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$remove_id]);
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Крафт Бокс — Крафтові товари Полтави</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Затемнення фону при відкритті кошика -->
    <div id="cart-overlay" onclick="toggleCart()"></div>

    <div class="header-logo">
        <h1>Craft Box</h1>
        <!-- Іконка кошика -->
        <div class="cart-icon-wrapper" onclick="toggleCart()">
            <img src="uploads/cart.png" alt="Кошик" class="cart-icon-svg">
            <?php if (!empty($_SESSION['cart'])): ?>
                <span class="cart-badge"><?php echo array_sum($_SESSION['cart']); ?></span>
            <?php endif; ?>
        </div>
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

    <!-- ВИЇЗНЕ МЕНЮ КОШИКА -->
    <div id="side-cart" class="side-cart">
        <div class="side-cart-header">
            <h3>Ваш кошик</h3>
            <span class="close-cart" onclick="toggleCart()">&times;</span>
        </div>
        <div class="side-cart-content">
            <?php if (!empty($_SESSION['cart'])): ?>
                <ul class="cart-list">
                    <?php 
                    $total_sum = 0;
                    $ids = implode(',', array_keys($_SESSION['cart']));
                    $stmt_cart = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
                    
                    while($item = $stmt_cart->fetch()): 
                        $qty = $_SESSION['cart'][$item['id']];
                        $subtotal = $item['price'] * $qty;
                        $total_sum += $subtotal;
                    ?>
                        <li class="cart-item">
                            <div class="cart-item-info">
                                <strong><?php echo htmlspecialchars($item['title']); ?></strong><br>
                                <?php echo $qty; ?> шт. x <?php echo $item['price']; ?> грн
                            </div>
                            <a href="index.php?remove=<?php echo $item['id']; ?>" class="remove-link">Видалити</a>
                        </li>
                    <?php endwhile; ?>
                </ul>
                <div class="side-cart-footer">
                    <p class="total-price">Разом: <?php echo $total_sum; ?> грн</p>
                    <a href="checkout.php" class="buy-btn checkout-btn" style="width: 100%; margin: 20px 0 0 0;">Оформити замовлення</a>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding-top: 50px;">Кошик порожній.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Твій банер -->
    <div class="hero-banner">
        <img src="uploads/own.png" alt="Craft Box Banner">
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
                    <a href='index.php?add_to_cart={$row['id']}' class='buy-btn'>Додати в кошик</a>
                  </div>";
        }
        ?>
    </div>

    <script>
        function toggleCart() {
            document.getElementById('side-cart').classList.toggle('active');
            document.getElementById('cart-overlay').classList.toggle('active');
        }
    </script>
</body>
</html>