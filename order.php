<?php
require 'config.php';

// Отримання списку всіх областей для вибору
function getAllAreas() {
    $url = "https://decentralization.ua/graphql";
    $query = '{areas{id, title}}';
    $options = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/json\r\n", 'content' => json_encode(['query' => $query])]];
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    return $result ? json_decode($result, true)['data']['areas'] : [];
}

$areas = getAllAreas();
$product_id = $_GET['id'] ?? 0;
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Оформлення доставки</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>

<div class="card">
    <h2>Оформлення замовлення</h2>
    <form action="save_order.php" method="POST">
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">

        <label>Оберіть область:</label>
        <select id="area_select" name="area_title" required>
            <option value="">-- Оберіть область --</option>
            <?php foreach ($areas as $area): ?>
                <option value="<?php echo $area['title']; ?>" <?php echo ($area['id'] == 16) ? 'selected' : ''; ?>>
                    <?php echo $area['title']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Введіть місто та вулицю:</label>
        <input type="text" id="address_input" name="address" placeholder="Наприклад: Полтава, вул. Соборності" required>
        
        <button type="submit">Підтвердити замовлення</button>
    </form>
    <a href="index.php">Назад</a>
</div>

<script>
$(function() {
    $("#address_input").autocomplete({
        source: function(request, response) {
            let selectedArea = $("#area_select").val();
            // Запит до OpenStreetMap Nominatim API
            $.getJSON("https://nominatim.openstreetmap.org/search", {
                q: request.term + ", " + selectedArea,
                format: "json",
                addressdetails: 1,
                limit: 5,
                "accept-language": "uk"
            }, function(data) {
                response($.map(data, function(item) {
                    return {
                        label: item.display_name,
                        value: item.display_name
                    };
                }));
            });
        },
        minLength: 3
    });
});
</script>

</body>
</html>