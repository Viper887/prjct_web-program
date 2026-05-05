<?php
require 'config.php'; 

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    if (!$email) {
        $error = "Введено невалідний формат email.";
    } elseif (mb_strlen($password) <= 8) {
        $error = "Пароль надто короткий. Справжній пароль має містити більше 8 символів.";
    } else {
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
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Вхід до системи</h2>
        <?php if ($error): ?> 
            <p style="color: #a31d1d; margin-bottom: 10px; font-weight: bold;"><?php echo $error; ?></p> 
        <?php endif; ?>
        
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required 
                   pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" 
                   title="Введіть ваш коректний email">
            <input type="password" name="password" placeholder="Пароль" required 
                   minlength="9" 
                   title="Пароль має містити більше 8 символів">
            <button type="submit">Увійти</button>
        </form>
        
        <p style="margin-top: 15px;">Ще немає акаунту? <a href="register.php" style="color: #a31d1d; font-weight: bold;">Зареєструватися</a></p>
    </div>
</body>
</html>