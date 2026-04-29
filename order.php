<?php
require 'config.php'; // Підключення бази та сесій

// --- 1. ПЕРШИЙ КОД (PHP функція для API) ---
function getAreaInfo($areaId) {
    $url = "https://decentralization.ua/graphql";
    $query = '{area(id: "'.$areaId.'"){title, square, population, local_community_count}}';
    
    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\n",
            'content' => json_encode(['query' => $query]),
            'ignore_errors' => true
        ]
    ];
    
    $context  = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    return $result ? json_decode($result, true) : null;
}

// Отримуємо дані про Полтавську область (ID 16)
$apiResponse = getAreaInfo("16");
$areaData = $apiResponse['data']['area'] ?? null;
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Оформлення доставки</title>
    <link rel="stylesheet" href="style.css">
    <!-- Підключаємо бібліотеки для автозаповнення адрес -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>

<div class="card">
    <h2>Оформлення замовлення</h2>

    <?php if ($areaData): ?>
        <p style="font-size: 0.85em; color: #8f94fb;">
            Регіон: <strong><?php echo $areaData['title']; ?></strong><br>
            Громад в області: <?php echo $areaData['local_community_count']; ?>
        </p>
    <?php endif; ?>

    <form action="save_order.php" method="POST">
        <label>Введіть адресу в Полтаві:</label>
        <!-- Поле з автозаповненням -->
        <input type="text" id="address" name="address" placeholder="Почніть вводити назву вулиці..." required>
        
        <input type="hidden" name="product_id" value="<?php echo $_GET['id'] ?? ''; ?>">
        <button type="submit">Підтвердити замовлення</button>
    </form>
    
    <a href="index.php">Скасувати</a>
</div>

<script>
// --- ІНТЕГРАЦІЯ АДРЕС ПОЛТАВИ ---
$(function() {
    // Завантажуємо твій файл streets.json
    $.getJSON("streets.json", function(data) {
        $("#address").autocomplete({
            source: data,
            minLength: 2 // Починати пошук після 2-х введених символів
        });
    });
});
</script>

</body>
</html>