<?php
require 'config.php'; 

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        
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
    <div class="auth-container"> <!-- Змінено клас на auth-container -->
        <h2>Вхід до системи</h2>
        <?php if ($error): ?> <p style="color: #a31d1d; margin-bottom: 10px;"><?php echo $error; ?></p> <?php endif; ?>
        
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Увійти</button>
        </form>
        
        <p style="margin-top: 15px;">Ще немає акаунту? <a href="register.php" style="color: #a31d1d; font-weight: bold;">Зареєструватися</a></p>
    </div>
</body>
</html>