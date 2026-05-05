<?php 
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password'])) {
        $name = htmlspecialchars(trim($_POST['name']));
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $password_raw = $_POST['password'];
        $role = $_POST['role'];

        // 1. Перевірка на валідність імейлу
        if (!$email) {
            $error = "Введено некоректний Email. Будь ласка, перевірте правильність написання.";
        } 
        // 2. Перевірка пароля на довжину (більше 8 символів — тобто мінімум 9 символів)
        elseif (mb_strlen($password_raw) <= 8) {
            $error = "Пароль має бути довшим за 8 символів.";
        } 
        // 3. Валідація ролі на випадок підміни коду в інспекторі
        elseif (!in_array($role, ['buyer', 'seller'])) {
            $error = "Некоректно обрано роль користувача.";
        } 
        else {
            $pass = password_hash($password_raw, PASSWORD_DEFAULT);

            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, $pass, $role]);
                header("Location: login.php");
                exit();
            } catch (PDOException $e) {
                $error = "Помилка: можливо, такий email вже зареєстрований.";
            }
        }
    } else {
        $error = "Будь ласка, заповніть усі поля форми!";
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Реєстрація</title>
</head>
<body>
    <div class="auth-container">
        <h2>Реєстрація</h2>
        <?php if ($error) echo "<p style='color: #a31d1d; margin-bottom: 10px; font-weight: bold;'>$error</p>"; ?>
        
        <form method="POST">
            <input type="text" name="name" placeholder="Ім'я" required>
            
            <input type="email" name="email" placeholder="Email" required 
                   pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" 
                   title="Будь ласка, введіть коректну адресу електронної пошти.">
            
            <input type="password" name="password" placeholder="Пароль (мінімум 9 символів)" required 
                   minlength="9" 
                   title="Пароль має містити більше 8 символів.">
            
            <select name="role">
                <option value="buyer">Покупець</option>
                <option value="seller">Продавець</option>
            </select>
            <button type="submit">Зареєструватися</button>
        </form>
        <p style="margin-top: 15px;">Вже є акаунт? <a href="login.php" style="color: #a31d1d; font-weight: bold;">Увійти</a></p>
    </div>
</body>
</html>