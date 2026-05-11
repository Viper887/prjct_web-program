<?php 
require 'config.php'; 

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    die("Доступ заборонено");
}

$success_msg = "";

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cropped_image'])){
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
        
        /* Стилі прев'ю переміщені вниз */
        #preview-container { 
            width: 100%; 
            height: 250px; 
            border: 2px dashed #ddd; 
            border-radius: 10px; 
            margin-top: 15px; /* Тепер зверху відступ */
            display: flex; 
            align-items: center; 
            justify-content: center; 
            overflow: hidden;
            background: #f9f9f9;
        }
        #preview-img { max-width: 100%; max-height: 100%; display: none; }
        .placeholder-text { color: #aaa; font-size: 14px; }

        .btn-file { border: 1px solid #000; padding: 12px; border-radius: 10px; width: 100%; display: block; text-align: center; cursor: pointer; }
        
        #cropper-modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            justify-content: center; align-items: center;
            flex-direction: column;
        }
        .cropper-container-box { width: 95%; max-width: 800px; background: white; padding: 20px; border-radius: 15px; }
        .cropper-area { max-height: 60vh; margin-bottom: 20px; overflow: hidden; }
        
        .submit-btn, .crop-save-btn { background-color: #a11e1e; color: white; border: none; padding: 15px; border-radius: 25px; width: 100%; font-weight: bold; cursor: pointer; }
        .crop-save-btn { margin-top: 10px; }
    </style>
</head>
<body class="add-product-body">

    <div class="header-logo">
        <h1>Craft Box</h1>
        <a href="index.php" class="back-link">← На головну</a>
    </div>

    <div class="add-card">
        <?php if($success_msg): ?>
            <div class="success-banner" style="color: green; text-align: center; margin-bottom: 15px;"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <form id="product-form" method="POST">
            <div class="form-group">
                <label>Назва товару</label>
                <input type="text" name="title" required placeholder="Введіть назву">
            </div>

            <div class="form-group">
                <label>Ціна (грн)</label>
                <input type="number" name="price" step="0.01" required placeholder="0.00">
            </div>

            <div class="form-group">
                <label>Фото товару</label>
                <label for="image-input" class="btn-file">Обрати фото</label>
                <input type="file" id="image-input" accept="image/*" style="display:none;">
                <input type="hidden" name="cropped_image" id="cropped_image_input">
                
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

        imageInput.addEventListener('change', function(e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    cropperImg.src = event.target.result;
                    cropperModal.style.display = 'flex';
                    
                    if (cropper) cropper.destroy();
                    
                    // Налаштування для вільної обрізки
                    cropper = new Cropper(cropperImg, {
                        aspectRatio: NaN, // Дозволяє змінювати пропорції як завгодно (NaN = вільний)
                        viewMode: 1,      // Обмежує рамку межами зображення
                        autoCropArea: 1,  // При відкритті виділяє все фото
                        background: true,
                        movable: true,
                        zoomable: true,
                        scalable: true
                    });
                };
                reader.readAsDataURL(files[0]);
            }
        });

        document.getElementById('save-crop').addEventListener('click', function() {
            // Отримуємо полотно з обрізаним зображенням
            // Якщо не вказувати width/height тут, збережеться оригінальний розмір обрізаної області
            const canvas = cropper.getCroppedCanvas();

            const base64Image = canvas.toDataURL('image/png');
            
            previewImg.src = base64Image;
            previewImg.style.display = 'block';
            placeholderText.style.display = 'none';
            
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