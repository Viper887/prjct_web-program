<?php 
require 'config.php'; 

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    die("Доступ заборонено");
}

$success_msg = "";
$error_msg = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // Серверна перевірка на заповненість усіх полів
    if(empty($_POST['title']) || empty($_POST['price']) || empty($_POST['cropped_image'])) {
        $error_msg = "Будь ласка, заповніть усі поля та додайте фото!";
    } else {
        $imgData = $_POST['cropped_image'];
        $imgData = str_replace('data:image/png;base64,', '', $imgData);
        $imgData = str_replace(' ', '+', $imgData);
        $data = base64_decode($imgData);

        $fileName = time() . "_product.png";
        $path = "uploads/" . $fileName;

        if (!is_dir('uploads')) mkdir('uploads', 0777, true);

        if(file_put_contents($path, $data)) {
            $stmt = $pdo->prepare("INSERT INTO products (seller_id, title, price, image_path) VALUES (?,?,?,?)");
            if($stmt->execute([$_SESSION['user_id'], $_POST['title'], $_POST['price'], $path])) {
                $success_msg = "Товар успішно додано!";
            }
        } else {
            $error_msg = "Помилка при збереженні зображення.";
        }
    }
} 
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Додати товар | Craft Box</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <style>
        .add-product-body { background-color: #e6e2dd; min-height: 100vh; display: flex; flex-direction: column; align-items: center; padding-bottom: 50px; }
        .header-logo { width: 100%; background-color: #a11e1e; padding: 15px 0; text-align: center; color: white; margin-bottom: 40px; }
        .back-link { color: white; text-decoration: none; font-size: 14px; opacity: 0.8; }
        .add-card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 450px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; font-size: 14px; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; outline: none; }
        #preview-container { 
            width: 100%; height: 250px; border: 2px dashed #ddd; border-radius: 10px; 
            margin-top: 15px; display: flex; align-items: center; justify-content: center; 
            overflow: hidden; background: #f9f9f9;
        }
        #preview-img { max-width: 100%; max-height: 100%; display: none; }
        .placeholder-text { color: #aaa; font-size: 14px; }
        .btn-file { border: 1px solid #000; padding: 12px; border-radius: 10px; width: 100%; display: block; text-align: center; cursor: pointer; }
        #cropper-modal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center; flex-direction: column;
        }
        .cropper-container-box { width: 95%; max-width: 800px; background: white; padding: 20px; border-radius: 15px; }
        .cropper-area { max-height: 60vh; margin-bottom: 20px; overflow: hidden; }
        .submit-btn, .crop-save-btn { background-color: #a11e1e; color: white; border: none; padding: 15px; border-radius: 25px; width: 100%; font-weight: bold; cursor: pointer; }
        .error-banner { color: #a11e1e; background: #fdeaea; padding: 10px; border-radius: 10px; text-align: center; margin-bottom: 15px; border: 1px solid #a11e1e; }
    </style>
</head>
<body class="add-product-body">

    <div class="header-logo">
        <h1>Craft Box</h1>
        <a href="index.php" class="back-link">← На головну</a>
    </div>

    <div class="add-card">
        <!-- Вивід повідомлень -->
        <?php if($success_msg): ?>
            <div style="color: green; text-align: center; margin-bottom: 15px; font-weight: bold;"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <?php if($error_msg): ?>
            <div class="error-banner"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <!-- Форма з валідацією HTML5 (required) -->
        <form id="product-form" method="POST">
            <div class="form-group">
                <label>Назва товару</label>
                <input type="text" name="title" required placeholder="Введіть назву">
            </div>

            <div class="form-group">
                <label>Ціна (грн)</label>
                <input type="number" name="price" step="0.01" min="0.01" required placeholder="0.00">
            </div>

            <div class="form-group">
                <label>Фото товару</label>
                <label for="image-input" class="btn-file">Обрати фото</label>
                <!-- Поле файлу не обов'язкове (required), бо ми перевіряємо приховане поле cropped_image -->
                <input type="file" id="image-input" accept="image/*" style="display:none;">
                
                <!-- Це поле ОБО'ЯЗКОВО має бути заповнене для відправки форми -->
                <input type="hidden" name="cropped_image" id="cropped_image_input" required>
                
                <div id="preview-container">
                    <img id="preview-img" src="#" alt="Прев'ю">
                    <span class="placeholder-text">Прев'ю обрізаного фото</span>
                </div>
            </div>

            <button type="submit" class="submit-btn">Додати товар</button>
        </form>
    </div>

    <div id="cropper-modal">
        <div class="cropper-container-box">
            <div class="cropper-area">
                <img id="cropper-img" style="max-width: 100%;">
            </div>
            <button type="button" class="crop-save-btn" id="save-crop">Зберегти область</button>
            <button type="button" style="background:none; border:none; color:gray; width:100%; margin-top:10px; cursor:pointer;" onclick="closeModal()">Скасувати</button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script>
        let cropper;
        const imageInput = document.getElementById('image-input');
        const cropperModal = document.getElementById('cropper-modal');
        const cropperImg = document.getElementById('cropper-img');
        const previewImg = document.getElementById('preview-img');
        const placeholderText = document.querySelector('.placeholder-text');
        const croppedInput = document.getElementById('cropped_image_input');
        const productForm = document.getElementById('product-form');

        // Клієнтська валідація при спробі відправити форму
        productForm.addEventListener('submit', function(e) {
            if (!croppedInput.value) {
                e.preventDefault(); // Зупиняємо відправку
                alert("Будь ласка, оберіть та збережіть фото товару!");
            }
        });

        imageInput.addEventListener('change', function(e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    cropperImg.src = event.target.result;
                    cropperModal.style.display = 'flex';
                    if (cropper) cropper.destroy();
                    cropper = new Cropper(cropperImg, {
                        aspectRatio: NaN,
                        viewMode: 1,
                        autoCropArea: 1,
                        background: true
                    });
                };
                reader.readAsDataURL(files[0]);
            }
        });

        document.getElementById('save-crop').addEventListener('click', function() {
            const canvas = cropper.getCroppedCanvas();
            const base64Image = canvas.toDataURL('image/png');
            
            previewImg.src = base64Image;
            previewImg.style.display = 'block';
            placeholderText.style.display = 'none';
            
            // Записуємо дані в приховане поле — тепер валідація пройде
            croppedInput.value = base64Image;
            
            closeModal();
        });

        function closeModal() {
            cropperModal.style.display = 'none';
            imageInput.value = ''; 
        }
    </script>
</body>
</html>