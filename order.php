<?php require 'config.php'; ?>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<form>
    <input id="address" name="address" placeholder="Адреса доставки (Полтава)">
    <button>Замовити</button>
</form>

<script>
$.getJSON("streets.json", function(data) {
    $("#address").autocomplete({ source: data });
});
</script>