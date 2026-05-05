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
            <div class="side-cart-title-block">
                <h3>Ваш кошик</h3>
                <?php 
                $total_items = !empty($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
                ?>
                <p class="side-cart-count">Всього товарів: <span><?php echo $total_items; ?></span></p>
            </div>
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
                            <div class="cart-item-image">
                                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            </div>
                            
                            <div class="cart-item-details">
                                <span class="cart-item-title"><?php echo htmlspecialchars($item['title']); ?></span>
                                <span class="cart-item-desc">крафтовий товар / опис</span> <div class="cart-item-controls">
                                    <div class="quantity-control">
                                        <a href="change_qty.php?id=<?php echo $item['id']; ?>&action=decrease" class="qty-btn">-</a>
                                        <span class="qty-num"><?php echo $qty; ?></span>
                                        <a href="change_qty.php?id=<?php echo $item['id']; ?>&action=increase" class="qty-btn">+</a>
                                    </div>
                                    <span class="cart-item-price"><?php echo number_format($subtotal, 2, '.', ''); ?> грн</span>
                                </div>
                            </div>
                            
                            <a href="index.php?remove=<?php echo $item['id']; ?>" class="cart-item-remove" title="Видалити">
                                <span>&times;</span>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
                
                <div class="side-cart-footer">
                    <div class="total-row">
                        <span>Всього до сплати:</span>
                        <span class="total-price"><?php echo number_format($total_sum, 2, '.', ''); ?> грн</span>
                    </div>
                    <a href="checkout.php" class="checkout-btn">Оформити замовлення</a>
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