<?php
require 'config.php';

// Перевірка наявності товарів у кошику. Якщо порожньо — повертаємо на головну
if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit;
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Очищення введених даних
    $name = htmlspecialchars(trim($_POST['name']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));

    if (!empty($name) && !empty($phone)) {
        try {
            // 1. Отримуємо дані про товари для розрахунку фінальної ціни
            $ids = array_keys($_SESSION['cart']);
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $products = $stmt->fetchAll();

            $total_price = 0;
            $items_for_json = [];

            foreach ($products as $product) {
                $qty = $_SESSION['cart'][$product['id']];
                $subtotal = $product['price'] * $qty;
                $total_price += $subtotal;
                
                $items_for_json[] = [
                    'product_id' => $product['id'],
                    'title' => $product['title'],
                    'price' => $product['price'],
                    'quantity' => $qty
                ];
            }

            // 2. Вставка замовлення у таблицю orders
            $sql = "INSERT INTO orders (user_id, customer_name, phone, address, total_price, items_json, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'new')";
            
            $stmt_insert = $pdo->prepare($sql);
            
            $user_id = $_SESSION['user_id'] ?? null; 
            $json_data = json_encode($items_for_json, JSON_UNESCAPED_UNICODE);

            if ($stmt_insert->execute([$user_id, $name, $phone, $address, $total_price, $json_data])) {
                // Очищуємо кошик після успішного замовлення
                unset($_SESSION['cart']);
                $success = true;
            } else {
                $error = "Не вдалося зберегти замовлення в базі даних.";
            }
        } catch (Exception $e) {
            $error = "Помилка: " . $e->getMessage();
        }
    } else {
        $error = "Будь ласка, заповніть обов'язкові поля: Ім'я та Телефон.";
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформлення замовлення — Craft Box</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="header-logo">
        <h1>Craft Box</h1>
    </div>

    <div class="cart-section">
        
        <?php if ($success): ?>
            <div style="text-align: center; padding: 40px;">
                <h2 style="color: #a31d1d; font-style: italic; font-size: 36px; text-transform: uppercase; margin-bottom: 20px;">Замовлення прийнято!</h2>
                <p style="font-size: 18px; margin-bottom: 30px;">Дякуємо! Ми зателефонуємо вам найближчим часом для підтвердження доставки.</p>
                <a href="index.php" class="buy-btn">Повернутися до магазину</a>
            </div>
        <?php else: ?>
            <h3>Оформлення замовлення</h3>
            
            <?php if ($error): ?>
                <div style="color: #a31d1d; background: #fff; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #a31d1d; font-weight: bold;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="checkout-form-grid">
                    <div class="form-group">
                        <label>Ваше ім'я *</label>
                        <input type="text" name="name" value="<?php echo $_SESSION['name'] ?? ''; ?>" placeholder="Введіть ваше ім'я" required>
                    </div>
                    <div class="form-group">
                        <label>Телефон *</label>
                        <input type="text" name="phone" placeholder="+380..." required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Адреса доставки</label>
                    <textarea name="address" rows="3" placeholder="Місто, номер відділення Нової Пошти або домашня адреса"></textarea>
                </div>

                <div class="order-summary">
                    <h4>Ваш вибір:</h4>
                    <table class="order-table">
                        <?php
                        $ids = array_keys($_SESSION['cart']);
                        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
                        $stmt_view = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
                        $stmt_view->execute($ids);
                        
                        $final_total = 0;
                        while($item = $stmt_view->fetch()):
                            $qty = $_SESSION['cart'][$item['id']];
                            $subtotal = $item['price'] * $qty;
                            $final_total += $subtotal;
                        ?>
                            <tr>
                                <td style="font-weight: bold;"><?php echo htmlspecialchars($item['title']); ?></td>
                                <td style="text-align: center; color: #666;"><?php echo $qty; ?> шт.</td>
                                <td style="text-align: right; font-weight: 800; color: #a31d1d;"><?php echo $subtotal; ?> грн</td>
                            </tr>
                        <?php endwhile; ?>
                        <tr>
                            <td colspan="2" style="padding-top: 20px; font-size: 20px; font-weight: 900;">ЗАГАЛЬНА СУМА:</td>
                            <td style="padding-top: 20px; text-align: right; font-size: 22px; font-weight: 900; color: #a31d1d;">
                                <?php echo $final_total; ?> грн
                            </td>
                        </tr>
                    </table>
                </div>

                <button type="submit" class="buy-btn" style="width: 100%;">
                    ПІДТВЕРДИТИ ЗАМОВЛЕННЯ
                </button>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="index.php" style="color: #666; text-decoration: none; font-size: 14px;">← Повернутися на головну</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>