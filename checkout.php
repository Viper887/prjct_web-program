<?php
require 'config.php';

// Перевірка кошика
if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit;
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $delivery_type = $_POST['delivery_type'] ?? 'np';
    $payment_method = $_POST['payment_method'] ?? 'cod';

    // Серверна валідація (про всяк випадок)
    if (empty($name) || !preg_match('/^\+380\d{9}$/', $phone)) {
        $error = "Некоректні дані. Перевірте номер телефону.";
    } else {
        // Формування адреси
        if ($delivery_type === 'np') {
            $city = htmlspecialchars(trim($_POST['np_city_name'] ?? ''));
            $office = htmlspecialchars(trim($_POST['np_office'] ?? ''));
            $full_address = "Нова Пошта: м. $city, $office";
        } else {
            $city = htmlspecialchars(trim($_POST['home_city'] ?? ''));
            $street = htmlspecialchars(trim($_POST['home_street'] ?? ''));
            $house = htmlspecialchars(trim($_POST['home_house'] ?? ''));
            $flat = htmlspecialchars(trim($_POST['home_flat'] ?? ''));
            $full_address = "Кур'єр: м. $city, вул. $street, буд. $house, кв. $flat";
        }

        try {
            $ids = array_keys($_SESSION['cart']);
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $products = $stmt->fetchAll();

            $total_price = 0;
            $items_for_json = [];
            foreach ($products as $product) {
                $qty = $_SESSION['cart'][$product['id']];
                $total_price += $product['price'] * $qty;
                $items_for_json[] = [
                    'product_id' => $product['id'], 
                    'title' => $product['title'], 
                    'price' => $product['price'], 
                    'quantity' => $qty
                ];
            }

            $payment_info = ($payment_method === 'card') ? "Карта" : "При отриманні";
            $sql = "INSERT INTO orders (user_id, customer_name, phone, address, total_price, items_json, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'new')";
            
            $stmt_insert = $pdo->prepare($sql);
            $json_data = json_encode($items_for_json, JSON_UNESCAPED_UNICODE);
            $final_details = $full_address . " | Оплата: " . $payment_info;

            if ($stmt_insert->execute([$_SESSION['user_id'] ?? null, $name, $phone, $final_details, $total_price, $json_data])) {
                unset($_SESSION['cart']);
                $success = true;
            }
        } catch (Exception $e) { 
            $error = "Помилка бази даних: " . $e->getMessage(); 
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформлення замовлення</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .search-results {
            position: absolute;
            background: white;
            border: 1px solid #ddd;
            width: 100%;
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .result-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            color: #333;
        }
        
        .result-item:hover { background: #f9f9f9; color: #ff6b6b; }
        .form-group { position: relative; margin-bottom: 15px; }
        select#np_office { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; background: white; }
        .error-msg { color: #a11e1e; background: #ffe6e6; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #ffb3b3; display: none; }
        .hidden-block { display: none; }
        .hidden-block.active { display: block; }
    </style>
</head>
<body>

<div class="header-logo">
    <div class="header-flex">
        <h1>Craft Box</h1>
        <a href="index.php" style="color: white; text-decoration: none; font-weight: bold; font-size: 14px;">← НА ГОЛОВНУ</a>
    </div>
</div>

<div class="checkout-container">
    <?php if ($success): ?>
        <div class="success-message" style="text-align: center; padding: 50px;">
            <h2 style="color: #28a745;">ЗАМОВЛЕННЯ ПРИЙНЯТО!</h2>
            <p>Дякуємо! Ми зателефонуємо вам найближчим часом.</p>
            <br><a href="index.php" class="buy-btn" style="text-decoration: none; background: #a11e1e; color: white; padding: 10px 20px; border-radius: 20px;">НА ГОЛОВНУ</a>
        </div>
    <?php else: ?>
        
        <div id="error_container" class="error-msg" <?php echo $error ? 'style="display:block;"' : ''; ?>>
            <?php echo $error; ?>
        </div>

        <form method="POST" id="mainOrderForm">
            <div class="checkout-grid">
                <div class="checkout-left">
                    <span class="section-title">1. Контактні дані</span>
                    <div class="form-group">
                        <label>Прізвище, Ім'я *</label>
                        <input type="text" name="name" id="name_input" placeholder="Введіть дані" value="<?php echo htmlspecialchars($name ?? ''); ?>" required minlength="2">
                    </div>
                    <div class="form-row" style="display: flex; gap: 10px;">
                        <div class="form-group" style="flex:1;">
                            <label>Телефон *</label>
                            <input type="text" name="phone" id="phone_input" value="<?php echo htmlspecialchars($phone ?? '+380'); ?>" maxlength="13" required>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Email</label>
                            <input type="email" name="email" placeholder="email@example.com" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                        </div>
                    </div>

                    <span class="section-title">2. Спосіб доставки</span>
                    <div class="selector-box" style="display: flex; gap: 10px; margin-bottom: 15px;">
                        <label class="opt active" onclick="toggleTab('delivery', 'np', this)">
                            <input type="radio" name="delivery_type" value="np" checked style="display:none;"> Нова Пошта
                        </label>
                        <label class="opt" onclick="toggleTab('delivery', 'home', this)">
                            <input type="radio" name="delivery_type" value="home" style="display:none;"> Кур'єр (Адреса)
                        </label>
                    </div>

                    <div id="np_fields" class="hidden-block active">
                        <div class="form-row" style="display: flex; gap: 10px;">
                            <div class="form-group" style="flex:1;">
                                <label>Місто</label>
                                <input type="text" id="np_city_search" placeholder="Почніть вводити..." autocomplete="off">
                                <div id="city_results" class="search-results"></div>
                                <input type="hidden" name="np_city_ref" id="np_city_ref">
                                <input type="hidden" name="np_city_name" id="np_city_name">
                            </div>
                            <div class="form-group" style="flex:1;">
                                <label>Відділення / Поштомат</label>
                                <select name="np_office" id="np_office" disabled>
                                    <option value="">Оберіть відділення</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="home_fields" class="hidden-block">
                        <div class="form-row" style="display: flex; gap: 10px;">
                            <div class="form-group" style="flex:1;"><label>Місто</label><input type="text" name="home_city" placeholder="Місто"></div>
                            <div class="form-group" style="flex:1;"><label>Вулиця</label><input type="text" name="home_street" placeholder="Вулиця"></div>
                        </div>
                        <div class="form-row" style="display: flex; gap: 10px;">
                            <div class="form-group" style="flex:1;"><label>Будинок</label><input type="text" name="home_house" placeholder="Буд."></div>
                            <div class="form-group" style="flex:1;"><label>Квартира</label><input type="text" name="home_flat" placeholder="Кв."></div>
                        </div>
                    </div>
                </div>

                <div class="checkout-right">
                    <span class="section-title">3. Спосіб оплати</span>
                    <div class="selector-box" style="display: flex; gap: 10px; margin-bottom: 15px;">
                        <label class="opt active" onclick="toggleTab('payment', 'cod', this)">
                            <input type="radio" name="payment_method" id="pay_cod" value="cod" checked style="display:none;"> При отриманні
                        </label>
                        <label class="opt" onclick="toggleTab('payment', 'card', this)">
                            <input type="radio" name="payment_method" id="pay_card" value="card" style="display:none;"> Банківська карта
                        </label>
                    </div>

                    <div id="card_fields" class="hidden-block">
                        <div class="form-group">
                            <label>Номер карти</label>
                            <input type="text" id="card_num" placeholder="0000 0000 0000 0000" maxlength="19">
                        </div>
                        <div class="form-row" style="display: flex; gap: 10px;">
                            <div class="form-group" style="flex:1;"><label>ММ/РР</label><input type="text" id="card_date" placeholder="12/25" maxlength="5"></div>
                            <div class="form-group" style="flex:1;"><label>CVV</label><input type="text" id="card_cvv" placeholder="123" maxlength="3"></div>
                        </div>
                    </div>

                    <div class="summary-card" style="background: #f4f1ee; padding: 20px; border-radius: 15px;">
                        <h4 style="margin: 0 0 10px 0;">РАЗОМ ДО СПЛАТИ:</h4>
                        <?php
                        $total = 0;
                        foreach ($_SESSION['cart'] as $id => $qty) {
                            $st = $pdo->prepare("SELECT price FROM products WHERE id = ?");
                            $st->execute([$id]);
                            $row = $st->fetch();
                            if ($row) $total += $row['price'] * $qty;
                        }
                        ?>
                        <div class="total-amount" style="font-size: 24px; font-weight: bold; color: #a11e1e; margin-bottom: 20px;"><?php echo $total; ?> грн</div>
                        <button type="submit" class="checkout-btn" style="width: 100%; background: #a11e1e; color: white; border: none; padding: 15px; border-radius: 25px; cursor: pointer; font-weight: bold;">ПІДТВЕРДИТИ</button>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
function toggleTab(category, type, element) {
    const parent = element.closest('.selector-box');
    parent.querySelectorAll('.opt').forEach(opt => opt.classList.remove('active'));
    element.classList.add('active');
    element.querySelector('input').checked = true;

    if (category === 'delivery') {
        document.getElementById('np_fields').classList.toggle('active', type === 'np');
        document.getElementById('home_fields').classList.toggle('active', type === 'home');
    }
    if (category === 'payment') {
        document.getElementById('card_fields').classList.toggle('active', type === 'card');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const citySearch = document.getElementById('np_city_search');
    const cityResults = document.getElementById('city_results');
    const officeSelect = document.getElementById('np_office');
    const form = document.getElementById('mainOrderForm');
    const errorContainer = document.getElementById('error_container');

    // --- ВАЛІДАЦІЯ ПЕРЕД ВІДПРАВКОЮ ---
    form.onsubmit = function(e) {
        let errors = [];
        const phone = document.getElementById('phone_input').value;
        const isCard = document.getElementById('pay_card').checked;

        // Перевірка телефону (має бути +380 і 9 цифр)
        if (phone.length < 13) {
            errors.push("Будь ласка, введіть повний номер телефону.");
        }

        // Перевірка карти (якщо обрано метод оплати "Карта")
        if (isCard) {
            const cNum = document.getElementById('card_num').value.replace(/\s/g, '');
            const cDate = document.getElementById('card_date').value;
            const cCvv = document.getElementById('card_cvv').value;

            if (cNum.length < 16) errors.push("Введіть коректний номер карти.");
            if (cDate.length < 5) errors.push("Введіть термін дії карти (ММ/РР).");
            if (cCvv.length < 3) errors.push("Введіть код CVV.");
        }

        // Якщо є помилки - виводимо їх без перезавантаження
        if (errors.length > 0) {
            e.preventDefault(); // Зупиняємо відправку форми
            errorContainer.innerHTML = errors.join('<br>');
            errorContainer.style.display = 'block';
            window.scrollTo({top: 0, behavior: 'smooth'});
            return false;
        }
    };

    citySearch.addEventListener('input', function() {
        let val = this.value.trim();
        document.getElementById('np_city_ref').value = '';
        document.getElementById('np_city_name').value = '';
        officeSelect.innerHTML = '<option value="">Оберіть відділення</option>';
        officeSelect.disabled = true;

        if (val.length < 2) {
            cityResults.innerHTML = '';
            return;
        }

        fetch(`np_api.php?action=getCities&q=${encodeURIComponent(val)}`)
            .then(r => r.json())
            .then(res => {
                let html = '';
                if (res.data && res.data.length > 0) {
                    res.data.forEach(city => {
                        html += `<div class="result-item" data-ref="${city.Ref}" data-name="${city.Description}">${city.Description} (${city.AreaDescription})</div>`;
                    });
                }
                cityResults.innerHTML = html;
            });
    });

    cityResults.addEventListener('click', function(e) {
        const item = e.target.closest('.result-item');
        if (item) {
            const ref = item.dataset.ref;
            const name = item.dataset.name;
            citySearch.value = name;
            document.getElementById('np_city_ref').value = ref;
            document.getElementById('np_city_name').value = name;
            cityResults.innerHTML = '';
            loadWarehouses(ref);
        }
    });

    function loadWarehouses(cityRef) {
        officeSelect.disabled = true;
        officeSelect.innerHTML = '<option>Завантаження...</option>';
        fetch(`np_api.php?action=getWarehouses&cityRef=${cityRef}`)
            .then(r => r.json())
            .then(res => {
                let html = '<option value="">Оберіть відділення...</option>';
                if (res.data) {
                    res.data.forEach(wh => {
                        html += `<option value="${wh.Description}">${wh.Description}</option>`;
                    });
                }
                officeSelect.innerHTML = html;
                officeSelect.disabled = false;
            });
    }

    document.addEventListener('click', (e) => {
        if (!citySearch.contains(e.target)) cityResults.innerHTML = '';
    });

    // Маска телефону
    const phoneInput = document.getElementById('phone_input');
    phoneInput.addEventListener('input', function (e) {
        if (!e.target.value.startsWith('+380')) e.target.value = '+380';
        e.target.value = '+' + e.target.value.replace(/[^\d]/g, '').substring(0, 12);
    });

    // Маска для номера карти (пробіли)
    document.getElementById('card_num').addEventListener('input', function (e) {
        let target = e.target;
        let position = target.selectionEnd;
        let length = target.value.length;
        target.value = target.value.replace(/[^\d]/g, '').replace(/(.{4})/g, '$1 ').trim();
        target.selectionEnd = position + (target.value.length > length ? 1 : 0);
    });
});
</script>
</body>
</html>