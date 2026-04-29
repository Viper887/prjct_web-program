<?php 
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Серверна перевірка: чи не пусті поля
    if (!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password'])) {
        
        $name = $_POST['name'];
        $email = $_POST['email'];
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $pass, $role]);
            header("Location: login.php");
            exit();
        } catch (PDOException $e) {
            $error = "Помилка: можливо, такий email вже зареєстрований.";
        }

    } else {
        $error = "Будь ласка, заповніть усі поля форми!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Реєстрація</title>
</head>
<body>
    <div class="card" style="max-width: 400px; margin: 50px auto;">
        <h2>Реєстрація</h2>
        <?php if ($error) echo "<p style='color: #ff6b6b;'>$error</p>"; ?>
        
        <form method="POST">
            <input name="name" placeholder="Ім'я" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <select name="role">
                <option value="buyer">Покупець</option>
                <option value="seller">Продавець</option>
            </select>
            <button type="submit">Зареєструватися</button>
        </form>
    </div>
</body>
</html>