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
    $delivery_type = htmlspecialchars(trim($_POST['delivery_type']));
    $payment_method = htmlspecialchars(trim($_POST['payment_method']));

    // --- ПЕРЕВІРКА (ВАЛІДАЦІЯ) ---
    if (empty($name) || mb_strlen($name) < 2) {
        $error = "Будь ласка, введіть коректне ім'я.";
    } elseif (!preg_match('/^\+380\d{9}$/', $phone)) {
        $error = "Некоректний номер телефону. Формат: +380XXXXXXXXX";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Введено некоректну адресу Email.";
    } else {
        // Формування адреси та перевірка міста
        if ($delivery_type === 'np') {
            $city = htmlspecialchars(trim($_POST['np_city_name']));
            $city_ref = htmlspecialchars(trim($_POST['np_city_ref']));
            $office = htmlspecialchars(trim($_POST['np_office']));
            
            if (empty($city) || empty($city_ref) || empty($office)) {
                $error = "Оберіть місто зі списку та вкажіть відділення.";
            }
            $full_address = "Нова Пошта: м. $city, $office";
        } else {
            $city = htmlspecialchars(trim($_POST['home_city']));
            $street = htmlspecialchars(trim($_POST['home_street']));
            $house = htmlspecialchars(trim($_POST['home_house']));
            if (empty($city) || empty($street) || empty($house)) {
                $error = "Для кур'єрської доставки заповніть місто, вулицю та будинок.";
            }
            $flat = htmlspecialchars(trim($_POST['home_flat']));
            $full_address = "Кур'єр: м. $city, вул. $street, буд. $house, кв. $flat";
        }
    }

    // 2. Якщо помилок немає — обробляємо замовлення
    if (empty($error)) {
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
        }
        .result-item:hover { background: #f9f9f9; color: #ff6b6b; }
        .form-group { position: relative; }
        select#np_office { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; background: white; }
        .error-msg { color: #a11e1e; background: #ffe6e6; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #ffb3b3; }
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
        <div class="success-message">
            <h2>ЗАМОВЛЕННЯ ПРИЙНЯТО!</h2>
            <p>Дякуємо! Ми зателефонуємо вам найближчим часом.</p>
            <br><a href="index.php" class="buy-btn">НА ГОЛОВНУ</a>
        </div>
    <?php else: ?>
        
        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" id="mainOrderForm">
            <div class="checkout-grid">
                <div class="checkout-left">
                    <span class="section-title">1. Контактні дані</span>
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label>Прізвище, Ім'я *</label>
                        <input type="text" name="name" placeholder="Введіть дані" value="<?php echo isset($name) ? $name : ''; ?>" required minlength="2">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Телефон *</label>
                            <input type="text" name="phone" id="phone_input" value="<?php echo isset($phone) ? $phone : '+380'; ?>" maxlength="13" required pattern="\+380[0-9]{9}">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" placeholder="email@example.com" value="<?php echo isset($email) ? $email : ''; ?>">
                        </div>
                    </div>

                    <span class="section-title">2. Спосіб доставки</span>
                    <div class="selector-box">
                        <label class="opt active" onclick="toggleTab('delivery', 'np', this)">
                            <input type="radio" name="delivery_type" value="np" checked> Нова Пошта
                        </label>
                        <label class="opt" onclick="toggleTab('delivery', 'home', this)">
                            <input type="radio" name="delivery_type" value="home"> Кур'єр (Адреса)
                        </label>
                    </div>

                    <div id="np_fields" class="hidden-block active">
                        <div class="form-row">
                            <div class="form-group" style="flex:1;">
                                <label>Місто</label>
                                <input type="text" id="np_city_search" placeholder="Оберіть місто..." autocomplete="off">
                                <input type="hidden" name="np_city_ref" id="np_city_ref">
                                <input type="hidden" name="np_city_name" id="np_city_name">
                                <div id="city_results" class="search-results"></div>
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
                        <div class="form-row">
                            <div class="form-group" style="flex:2;"><label>Місто</label><input type="text" name="home_city" placeholder="Місто"></div>
                            <div class="form-group" style="flex:2;"><label>Вулиця</label><input type="text" name="home_street" placeholder="Вулиця"></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>Будинок</label><input type="text" name="home_house" placeholder="Буд."></div>
                            <div class="form-group"><label>Квартира</label><input type="text" name="home_flat" placeholder="Кв."></div>
                        </div>
                    </div>
                </div>

                <div class="checkout-right">
                    <span class="section-title">3. Спосіб оплати</span>
                    <div class="selector-box">
                        <label class="opt active" onclick="toggleTab('payment', 'cod', this)">
                            <input type="radio" name="payment_method" value="При отриманні" checked> При отриманні
                        </label>
                        <label class="opt" onclick="toggleTab('payment', 'card', this)">
                            <input type="radio" name="payment_method" value="card"> Банківська карта
                        </label>
                    </div>

                    <div id="card_fields" class="hidden-block">
                        <div class="form-group" style="margin-bottom: 10px;">
                            <label>Номер карти</label>
                            <input type="text" id="card_num" name="card_num" placeholder="0000 0000 0000 0000" maxlength="19">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>ММ/РР</label>
                                <input type="text" id="card_date" name="card_date" placeholder="12/25" maxlength="5">
                            </div>
                            <div class="form-group">
                                <label>CVV</label>
                                <input type="text" id="card_cvv" name="card_cvv" placeholder="123" maxlength="3">
                            </div>
                        </div>
                    </div>

                    <div class="summary-card">
                        <h4 style="font-weight: 900; font-size: 14px; margin-bottom: 10px;">РАЗОМ ДО СПЛАТИ:</h4>
                        <?php
                        $total = 0;
                        if (!empty($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $id => $qty) {
                                $st = $pdo->prepare("SELECT price FROM products WHERE id = ?");
                                $st->execute([$id]);
                                $row = $st->fetch();
                                if ($row) $total += $row['price'] * $qty;
                            }
                        }
                        ?>
                        <div class="total-amount"><?php echo $total; ?> грн</div>
                        <button type="submit" class="checkout-btn" style="width: 100%;">ПІДТВЕРДИТИ</button>
                        <a href="index.php" class="btn-secondary">ПОВЕРНУТИСЯ ДО МАГАЗИНУ</a>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
function toggleTab(category, type, element) {
    element.closest('.selector-box').querySelectorAll('.opt').forEach(opt => opt.classList.remove('active'));
    element.classList.add('active');

    if (category === 'delivery') {
        document.getElementById('np_fields').classList.remove('active');
        document.getElementById('home_fields').classList.remove('active');
        document.getElementById(type + '_fields').classList.add('active');
        
        const npOffice = document.getElementById('np_office');
        if(type === 'np') {
            npOffice.setAttribute('required', 'required');
        } else {
            npOffice.removeAttribute('required');
        }
    }
    if (category === 'payment') {
        const cardFields = document.getElementById('card_fields');
        if (type === 'card') cardFields.classList.add('active');
        else cardFields.classList.remove('active');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const citySearch = document.getElementById('np_city_search');
    const cityResults = document.getElementById('city_results');
    const officeSelect = document.getElementById('np_office');
    const form = document.getElementById('mainOrderForm');

    // --- ПОШУК МІСТА ТА СКИНУТТЯ ДАНИХ ПРИ ВВОДІ ---
    citySearch.addEventListener('input', function() {
        let val = this.value.trim();
        
        // КРИТИЧНО: Скидаємо Ref та відділення при будь-якій зміні тексту
        document.getElementById('np_city_ref').value = '';
        document.getElementById('np_city_name').value = '';
        officeSelect.innerHTML = '<option value="">Оберіть місто</option>';
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

    // ВИБІР МІСТА
    cityResults.addEventListener('click', function(e) {
        if (e.target.classList.contains('result-item')) {
            const ref = e.target.dataset.ref;
            const name = e.target.dataset.name;
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

    // ВАЛІДАЦІЯ ПЕРЕД ВІДПРАВКОЮ
    form.onsubmit = function() {
        const deliveryType = document.querySelector('input[name="delivery_type"]:checked').value;
        if(deliveryType === 'np') {
            const cityRef = document.getElementById('np_city_ref').value;
            if(!cityRef) {
                alert('Будь ласка, оберіть місто зі списку підказок!');
                citySearch.focus();
                return false;
            }
            if(!officeSelect.value) {
                alert('Будь ласка, оберіть відділення!');
                officeSelect.focus();
                return false;
            }
        }
        return true;
    };

    document.addEventListener('click', (e) => {
        if (!citySearch.contains(e.target)) cityResults.innerHTML = '';
    });
});

// Маски введення
const phoneInput = document.getElementById('phone_input');
phoneInput.addEventListener('input', function (e) {
    if (!e.target.value.startsWith('+380')) e.target.value = '+380';
    e.target.value = '+' + e.target.value.replace(/[^\d]/g, '').substring(0, 12);
});

const cardNumInput = document.getElementById('card_num');
if(cardNumInput) {
    cardNumInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || '';
        e.target.value = formattedValue;
    });
}

const cardDateInput = document.getElementById('card_date');
if(cardDateInput) {
    cardDateInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/[^0-9]/gi, '');
        if (value.length > 2) e.target.value = value.substring(0, 2) + '/' + value.substring(2, 4);
        else e.target.value = value;
    });
}
</script>
</body>
</html>