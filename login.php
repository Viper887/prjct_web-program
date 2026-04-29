<?php
require 'config.php'; // Підключення до БД та запуск session_start()

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Підготовка запиту для безпеки
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Перевірка чи існує користувач та чи вірний пароль
    if ($user && password_verify($password, $user['password'])) {
        // Зберігаємо дані у сесію
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        
        // Перенаправлення на головну
        header("Location: index.php");
        exit();
    } else {
        $error = "Невірний email або пароль.";
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Вхід</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="card" style="max-width: 400px; margin: 50px auto;">
        <h2>Вхід до системи</h2>
        <?php if ($error): ?> <p style="color: red;"><?php echo $error; ?></p> <?php endif; ?>
        
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required style="width: 100%; margin-bottom: 10px; display: block;">
            <input type="password" name="password" placeholder="Пароль" required style="width: 100%; margin-bottom: 10px; display: block;">
            <button type="submit" style="width: 100%;">Увійти</button>
        </form>
        
        <p>Ще немає акаунту? <a href="register.php">Зареєструватися</a></p>
    </div>
</body>
</html>