<?php require 'config.php'; 
if($_SESSION['role'] !== 'seller') die("Доступ заборонено");
if($_FILES){
    $path = "uploads/" . time() . $_FILES['image']['name'];
    move_uploaded_file($_FILES['image']['tmp_name'], $path);
    $pdo->prepare("INSERT INTO products (seller_id, title, price, image_path) VALUES (?,?,?,?)")
        ->execute([$_SESSION['user_id'], $_POST['title'], $_POST['price'], $path]);
} ?>
<form method="POST" enctype="multipart/form-data">
    <input name="title" placeholder="Назва"><input name="price" placeholder="Ціна"><input type="file" name="image">
    <button>Додати товар</button>
</form>