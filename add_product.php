<?php 
require 'config.php'; 

// Перевірка ролі (якщо сесія вже запущена в config.php)
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    die("Доступ заборонено. Тільки для продавців.");
}

// Обробка форми
if($_SERVER['REQUEST_METHOD'] === 'POST' && $_FILES){
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $path = $upload_dir . time() . "_" . basename($_FILES['image']['name']);
    
    if(move_uploaded_file($_FILES['image']['tmp_name'], $path)) {
        $pdo->prepare("INSERT INTO products (seller_id, title, price, image_path) VALUES (?,?,?,?)")
            ->execute([$_SESSION['user_id'], $_POST['title'], $_POST['price'], $path]);
        $success = "Товар успішно додано!";
    }
} 
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Додати товар — Craft Box</title>
    <style>
        /* Стилі згідно з макетом image_b45e54.png */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background-color: #f5f3f0; /* Світло-бежевий фон */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Шапка сайту */
        .header {
            width: 100%;
            background-color: #a31d1d; /* Темно-червоний */
            padding: 15px 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .header h1 {
            color: white;
            font-size: 32px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header-icons {
            position: absolute;
            right: 40px;
            display: flex;
            gap: 20px;
        }

        .icon {
            width: 24px;
            height: 24px;
            fill: white;
        }

        /* Контейнер форми */
        .container {
            margin-top: 100px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            padding: 0 20px;
        }

        /* Поля вводу */
        .input-group {
            margin-bottom: 25px;
        }

        input[type="text"], 
        input[type="number"] {
            width: 100%;
            background: white;
            border: 1px solid #b5b5b5;
            border-radius: 8px;
            padding: 16px 20px;
            font-size: 18px;
            color: #333;
            outline: none;
        }

        input::placeholder {
            color: #888;
        }

        /* Кастомне поле для фото */
        .file-upload-label {
            display: block;
            width: 100%;
            background: white;
            border: 1px solid #b5b5b5;
            border-radius: 8px;
            padding: 16px 20px;
            font-size: 18px;
            color: #888;
            cursor: pointer;
            margin-bottom: 60px; /* Відступ перед кнопкою */
        }

        input[type="file"] {
            display: none;
        }

        /* Кнопка "Додати товар" */
        .btn-submit {
            background-color: #a31d1d;
            color: white;
            border: none;
            padding: 16px 60px;
            border-radius: 40px;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            box-shadow: 0 4px 15px rgba(163, 29, 29, 0.3);
        }

        .btn-submit:hover {
            background-color: #8a1818;
            transform: translateY(-2px);
        }

        .status-msg {
            margin-bottom: 20px;
            color: #a31d1d;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <header class="header">
        <h1>CRAFT BOX</h1>
        <div class="header-icons">
            <svg class="icon" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            <svg class="icon" viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
        </div>
    </header>

    <div class="container">
        <?php if (isset($success)): ?>
            <div class="status-msg"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <input type="text" name="title" placeholder="Назва товару" required>
            </div>
            
            <div class="input-group">
                <input type="number" name="price" placeholder="Ціна" step="0.01" required>
            </div>

            <label for="image-input" class="file-upload-label" id="file-label">
                Додати фото
            </label>
            <input type="file" name="image" id="image-input" accept="image/*" required onchange="updateLabel()">

            <button type="submit" class="btn-submit">Додати товар</button>
        </form>
    </div>

    <script>
        // Скрипт для відображення назви файлу після вибору
        function updateLabel() {
            const input = document.getElementById('image-input');
            const label = document.getElementById('file-label');
            if (input.files.length > 0) {
                label.textContent = input.files[0].name;
                label.style.color = "#333";
            }
        }
    </script>
</body>
</html>