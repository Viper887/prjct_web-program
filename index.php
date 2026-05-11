<?php 
require 'config.php'; 

// --- 1. ЛОГІКА ОБРОБКИ (AJAX ТА ЗВИЧАЙНА) ---
if (isset($_GET['ajax']) || isset($_GET['add_to_cart']) || isset($_GET['remove']) || isset($_GET['action'])) {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    // Додавання в кошик
    if (isset($_GET['add_to_cart'])) {
        $id = (int)$_GET['add_to_cart'];
        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    }

    // Зміна кількості (+/-)
    if (isset($_GET['action']) && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        if ($_GET['action'] == 'increase') {
            $_SESSION['cart'][$id]++;
        } elseif ($_GET['action'] == 'decrease') {
            if ($_SESSION['cart'][$id] > 1) $_SESSION['cart'][$id]--;
            else unset($_SESSION['cart'][$id]);
        }
    }

    // Видалення
    if (isset($_GET['remove'])) {
        unset($_SESSION['cart'][(int)$_GET['remove']]);
    }

    // Якщо це AJAX запит, повертаємо лише вміст кошика
    if (isset($_GET['ajax'])) {
        ob_start();
        include_cart_content($pdo);
        $html = ob_get_clean();
        
        header('Content-Type: application/json');
        echo json_encode([
            'html' => $html,
            'total_count' => !empty($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0
        ]);
        exit;
    } else {
        header("Location: index.php");
        exit;
    }
}

// Функція для рендеру вмісту кошика (щоб не дублювати код)
function include_cart_content($pdo) {
    if (!empty($_SESSION['cart'])): ?>
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
                        <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="">
                    </div>
                    <div class="cart-item-details">
                        <span class="cart-item-title"><?= htmlspecialchars($item['title']) ?></span>
                        <span class="cart-item-desc">Крафтовий товар</span> 
                        <div class="cart-item-controls">
                            <div class="quantity-control">
                                <a href="index.php?id=<?= $item['id'] ?>&action=decrease" class="qty-btn">-</a>
                                <span class="qty-num"><?= $qty ?></span>
                                <a href="index.php?id=<?= $item['id'] ?>&action=increase" class="qty-btn">+</a>
                            </div>
                            <span class="cart-item-price"><?= number_format($subtotal, 2, '.', '') ?> грн</span>
                        </div>
                    </div>
                    <a href="index.php?remove=<?= $item['id'] ?>" class="cart-item-remove">
                        <span>&times;</span>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
        <div class="side-cart-footer">
            <div class="total-row">
                <span>Всього до сплати:</span>
                <span class="total-price"><?= number_format($total_sum, 2, '.', '') ?> грн</span>
            </div>
            <a href="checkout.php" class="checkout-btn">Оформити замовлення</a>
        </div>
    <?php else: ?>
        <p style="text-align: center; color: #666; padding-top: 50px;">Кошик порожній.</p>
    <?php endif;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Крафт Бокс — Крафтові товари Полтави</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* ВАШІ СТИЛІ БЕЗ ЗМІН */
        .seller-link { display: block; font-size: 13px; color: #666; text-decoration: none; margin-bottom: 10px; transition: 0.3s; }
        .seller-link:hover { color: #a11e1e; text-decoration: underline; }
        .seller-link b { color: #333; }
        .my-profile-btn { font-weight: bold; color: #a11e1e; text-decoration: none; }

        .side-cart-content { transition: opacity 0.2s; }
        .loading { opacity: 0.5; pointer-events: none; }
    </style>
</head>
<body>

    <div id="cart-overlay" onclick="toggleCart()"></div>

    <div class="header-logo">
        <h1>Craft Box</h1>
        <div class="cart-icon-wrapper" onclick="toggleCart()">
            <img src="uploads/cart.png" alt="Кошик" class="cart-icon-svg">
            <span id="cart-badge-container">
                <?php if (!empty($_SESSION['cart'])): ?>
                    <span class="cart-badge"><?php echo array_sum($_SESSION['cart']); ?></span>
                <?php endif; ?>
            </span>
        </div>
    </div>

<div class="user-menu">
    <?php if (isset($_SESSION['user_id'])): ?>
        <p>Вітаємо, <?php echo htmlspecialchars($_SESSION['name']); ?>! | 
           <?php 
           // Визначаємо файл профілю залежно від ролі
           $profile_page = ($_SESSION['role'] == 'seller') ? 'seller_profile.php' : 'customer_profile.php';
           ?>
           <a href="<?php echo $profile_page; ?>?id=<?php echo $_SESSION['user_id']; ?>" class="my-profile-btn">Мій кабінет</a> | 
           <a href="logout.php">Вийти</a>
        </p>
        
        <?php if ($_SESSION['role'] == 'seller'): ?>
            <a href="add_product.php">Додати товар</a> | 
            <a href="admin_orders.php"><b>Переглянути замовлення</b></a>
        <?php endif; ?>

    <?php else: ?>
        <a href="login.php">Увійти</a> | <a href="register.php">Реєстрація</a>
    <?php endif; ?>
</div>

    <div id="side-cart" class="side-cart">
        <div class="side-cart-header">
            <div class="side-cart-title-block">
                <h3>Ваш кошик</h3>
                <p class="side-cart-count">Всього товарів: <span id="side-cart-total-qty"><?= !empty($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0 ?></span></p>
            </div>
            <span class="close-cart" onclick="toggleCart()">&times;</span>
        </div>
        
        <div class="side-cart-content" id="ajax-cart-container">
            <?php include_cart_content($pdo); ?>
        </div>
    </div>

    <div class="hero-banner">
        <img src="uploads/own.png" alt="Craft Box Banner">
    </div>

    <h2 class="catalog-title">Каталог</h2>

    <div class="products-container">
        <?php
        $stmt = $pdo->query("SELECT p.*, u.name as seller_name FROM products p JOIN users u ON p.seller_id = u.id");
        while($row = $stmt->fetch()): ?>
            <div class='card'>
                <img src='<?php echo htmlspecialchars($row['image_path']); ?>' alt=''>
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <a href="seller_profile.php?id=<?php echo $row['seller_id']; ?>" class="seller-link">
                    Продавець: <b><?php echo htmlspecialchars($row['seller_name']); ?></b>
                </a>
                <p class='price'><?php echo number_format($row['price'], 2, '.', ''); ?> грн</p>
                <a href="index.php?add_to_cart=<?= $row['id'] ?>" class="buy-btn ajax-action">Додати в кошик</a>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        function toggleCart() {
            document.getElementById('side-cart').classList.toggle('active');
            document.getElementById('cart-overlay').classList.toggle('active');
        }

        // Обробка всіх кліків кошика (додати, + , - , видалити) через AJAX
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.ajax-action, .qty-btn, .cart-item-remove, .buy-btn');
            if (!btn) return;

            const url = btn.getAttribute('href');
            if (!url || url === 'javascript:void(0)') return;

            e.preventDefault();
            const container = document.getElementById('ajax-cart-container');
            container.classList.add('loading');

            fetch(url + '&ajax=1')
                .then(res => res.json())
                .then(data => {
                    container.innerHTML = data.html;
                    container.classList.remove('loading');
                    
                    // Оновлення цифр на іконці та в заголовку
                    document.getElementById('side-cart-total-qty').innerText = data.total_count;
                    const badgeContainer = document.getElementById('cart-badge-container');
                    if (data.total_count > 0) {
                        badgeContainer.innerHTML = `<span class="cart-badge">${data.total_count}</span>`;
                    } else {
                        badgeContainer.innerHTML = '';
                    }

                    // Відкриваємо кошик, якщо додали товар з каталогу
                    if (btn.classList.contains('buy-btn') && !document.getElementById('side-cart').classList.contains('active')) {
                        toggleCart();
                    }
                });
        });
    </script>
</body>
</html>