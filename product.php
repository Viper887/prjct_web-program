<?php
require 'config.php';

// --- 1. ЛОГІКА ОБРОБКИ AJAX (Для кошика) ---
if (isset($_GET['ajax']) && (isset($_GET['add_to_cart']) || isset($_GET['remove']) || isset($_GET['action']))) {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    $id = (int)($_GET['add_to_cart'] ?? $_GET['id'] ?? $_GET['remove'] ?? 0);

    if (isset($_GET['add_to_cart'])) {
        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    }
    if (isset($_GET['action'])) {
        if ($_GET['action'] == 'increase') $_SESSION['cart'][$id]++;
        elseif ($_GET['action'] == 'decrease') {
            if ($_SESSION['cart'][$id] > 1) $_SESSION['cart'][$id]--;
            else unset($_SESSION['cart'][$id]);
        }
    }
    if (isset($_GET['remove'])) unset($_SESSION['cart'][$id]);

    ob_start();
    include_cart_content($pdo);
    $html = ob_get_clean();
    
    header('Content-Type: application/json');
    echo json_encode([
        'html' => $html,
        'total_count' => !empty($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0
    ]);
    exit;
}

// Функція для контенту кошика (така ж, як в index.php)
function include_cart_content($pdo) {
    if (!empty($_SESSION['cart'])): ?>
        <ul class="cart-list">
            <?php 
            $total_sum = 0;
            $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
            $stmt_cart = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
            while($item = $stmt_cart->fetch()): 
                $qty = $_SESSION['cart'][$item['id']];
                $subtotal = $item['price'] * $qty;
                $total_sum += $subtotal;
            ?>
                <li class="cart-item">
                    <div class="cart-item-image"><img src="<?= $item['image_path'] ?>"></div>
                    <div class="cart-item-details">
                        <span class="cart-item-title"><?= htmlspecialchars($item['title']) ?></span>
                        <div class="cart-item-controls">
                            <div class="quantity-control">
                                <button onclick="updateCart(<?= $item['id'] ?>, 'decrease')" class="qty-btn">-</button>
                                <span class="qty-num"><?= $qty ?></span>
                                <button onclick="updateCart(<?= $item['id'] ?>, 'increase')" class="qty-btn">+</button>
                            </div>
                            <span class="cart-item-price"><?= number_format($subtotal, 2, '.', '') ?> грн</span>
                        </div>
                    </div>
                    <button onclick="updateCart(<?= $item['id'] ?>, 'remove')" class="cart-item-remove">&times;</button>
                </li>
            <?php endwhile; ?>
        </ul>
        <div class="side-cart-footer">
            <div class="total-row"><span>Всього:</span><strong><?= number_format($total_sum, 2, '.', '') ?> грн</strong></div>
            <a href="checkout.php" class="checkout-btn">Оформити замовлення</a>
        </div>
    <?php else: ?>
        <p style="text-align: center; color: #666; padding-top: 50px;">Кошик порожній.</p>
    <?php endif;
}

// --- 2. ОТРИМАННЯ ДАНИХ ТОВАРУ ---
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Додаємо JOIN, щоб отримати ім'я продавця
$stmt = $pdo->prepare("SELECT p.*, u.name as seller_name FROM products p JOIN users u ON p.seller_id = u.id WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if(!$product) die("Товар не знайдено.");
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['title']) ?> | Craft Box</title>
    <link rel="stylesheet" href="style.css"> <style>
        body { background-color: #e6e2dd; margin: 0; font-family: sans-serif; }
        
        /* HEADER (ЯК В INDEX) */
        .header { background-color: #a11e1e; padding: 15px 40px; display: flex; justify-content: center; align-items: center; position: relative; color: white; }
        .header h1 { margin: 0; font-size: 35px; letter-spacing: 2px; font-weight: 900; }
        .header-actions { position: absolute; right: 40px; display: flex; align-items: center; gap: 20px; }
        .header-actions img { width: 28px; filter: brightness(0) invert(1); cursor: pointer; }

        /* DROP-DOWN МЕНЮ */
        .user-dropdown-container { position: relative; }
        .user-dropdown-content {
            display: none; position: absolute; right: 0; top: 100%; background-color: #fff;
            min-width: 200px; box-shadow: 0px 8px 16px rgba(0,0,0,0.1); z-index: 1000;
            border-radius: 8px; padding: 10px 0; margin-top: 15px; border: 1px solid #eee;
        }
        .user-dropdown-content.active { display: block; }
        .user-dropdown-content a, .user-dropdown-content p { color: #333; padding: 10px 16px; text-decoration: none; display: block; text-align: center; font-size: 14px; }
        .user-dropdown-content a:hover { background-color: #f9f9f9; color: #a11e1e; }

        /* КОНТЕНТ */
        .container { max-width: 1100px; margin: 40px auto; padding: 20px; }
        .product-title { text-align: center; font-size: 34px; font-weight: bold; margin-bottom: 40px; text-transform: uppercase; }

        .content-flex { display: flex; gap: 60px; align-items: start; }
        .image-side { flex: 1; background: white; border-radius: 40px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .image-side img { width: 100%; border-radius: 20px; display: block; }

        .info-side { flex: 1; }
        .desc-label { font-size: 28px; font-weight: bold; margin-bottom: 20px; }
        .desc-text { font-size: 16px; color: #444; line-height: 1.6; margin-bottom: 20px; }
        
        .seller-info { margin-bottom: 25px; font-size: 18px; }
        .seller-info a { color: #a11e1e; text-decoration: none; font-weight: bold; }
        .seller-info a:hover { text-decoration: underline; }

        .weight-tag { font-size: 22px; font-weight: bold; margin-bottom: 20px; color: #333; }
        .price-tag { font-size: 32px; font-weight: bold; margin-bottom: 40px; }

        .add-btn { 
            background: #a11e1e; color: white; border: none; padding: 20px 0; 
            width: 100%; max-width: 350px; border-radius: 50px; 
            font-size: 22px; font-weight: bold; cursor: pointer; transition: 0.3s;
        }
        .add-btn:hover { background: #851919; transform: scale(1.02); }

        /* Badge для кошика */
        .cart-icon-wrapper { position: relative; }
        .cart-badge { position: absolute; top: -8px; right: -8px; background: white; color: #a11e1e; font-size: 12px; font-weight: bold; border-radius: 50%; padding: 2px 6px; }

        @media (max-width: 800px) { .content-flex { flex-direction: column; } .add-btn { max-width: 100%; } }
    </style>
</head>
<body>

<div id="cart-overlay" onclick="toggleCart()"></div>

<div class="header">
    <h1>CRAFT BOX</h1>
    <div class="header-actions">
        <div class="cart-icon-wrapper" onclick="toggleCart()">
            <img src="uploads/cart.png" alt="Кошик">
            <span id="cart-badge-container">
                <?php if (!empty($_SESSION['cart'])): ?>
                    <span class="cart-badge"><?= array_sum($_SESSION['cart']) ?></span>
                <?php endif; ?>
            </span>
        </div>

        <div class="user-dropdown-container">
            <img src="uploads/user-icon.png" alt="Профіль" class="user-icon-img" onclick="toggleUserMenu(event)">
            <div id="userDropdown" class="user-dropdown-content">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <p class="welcome-text">Вітаємо, <?= htmlspecialchars($_SESSION['name']); ?>!</p>
                    <a href="<?= ($_SESSION['role'] == 'seller' ? 'seller_profile.php' : 'customer_profile.php') ?>?id=<?= $_SESSION['user_id'] ?>">Мій кабінет</a>
                    <a href="logout.php" style="color: #a11e1e; border-top: 1px solid #eee;">Вийти</a>
                <?php else: ?>
                    <a href="login.php">Увійти</a>
                    <a href="register.php">Реєстрація</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div id="side-cart" class="side-cart">
    <div class="side-cart-header">
        <h3>Ваш кошик</h3>
        <span class="close-cart" onclick="toggleCart()">&times;</span>
    </div>
    <div class="side-cart-content" id="ajax-cart-container">
        <?php include_cart_content($pdo); ?>
    </div>
</div>

<div class="container">
    <a href="index.php" style="text-decoration:none; color:#666; font-size:14px;">← Повернутися до каталогу</a>
    <h2 class="product-title"><?= htmlspecialchars($product['title']) ?></h2>

    <div class="content-flex">
        <div class="image-side">
            <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="">
        </div>

        <div class="info-side">
            <div class="desc-label">Опис</div>
            <div class="desc-text"><?= nl2br(htmlspecialchars($product['description'] ?: "Опис відсутній.")) ?></div>

            <div class="seller-info">
                Продавець: <a href="seller_profile.php?id=<?= $product['seller_id'] ?>"><?= htmlspecialchars($product['seller_name']) ?></a>
            </div>

            <div class="weight-tag">
                Вага: <?= htmlspecialchars($product['weight']) ?> гр
            </div>

            <div class="price-tag">
                Ціна: <?= number_format($product['price'], 0, '.', '') ?> грн
            </div>

            <button class="add-btn" onclick="updateCart(<?= $product['id'] ?>, 'add')">Додати в кошик</button>
        </div>
    </div>
</div>

<script>
// ЛОГІКА КОШИКА (AJAX)
function updateCart(id, action) {
    let url = 'product.php?ajax=1';
    if(action === 'add') url += '&add_to_cart=' + id;
    else if(action === 'remove') url += '&remove=' + id;
    else url += '&id=' + id + '&action=' + action;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            document.getElementById('ajax-cart-container').innerHTML = data.html;
            const badgeContainer = document.getElementById('cart-badge-container');
            if (data.total_count > 0) {
                badgeContainer.innerHTML = `<span class="cart-badge">${data.total_count}</span>`;
            } else {
                badgeContainer.innerHTML = '';
            }
            if(action === 'add' && !document.getElementById('side-cart').classList.contains('active')) {
                toggleCart();
            }
        });
}

function toggleCart() {
    document.getElementById('side-cart').classList.toggle('active');
    document.getElementById('cart-overlay').classList.toggle('active');
}

function toggleUserMenu(e) {
    e.stopPropagation();
    document.getElementById('userDropdown').classList.toggle('active');
}

window.onclick = function(event) {
    if (!event.target.matches('.user-icon-img')) {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown && dropdown.classList.contains('active')) dropdown.classList.remove('active');
    }
}
</script>

</body>
</html>