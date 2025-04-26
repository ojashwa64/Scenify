<?php
// Configuration
$uploadDir = 'wallpapers/';
$dataFile = 'data/wallpapers.json';
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
$maxSize = 10 * 1024 * 1024; // 10MB

// Create directories if they don't exist
if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
if (!file_exists('data')) mkdir('data', 0755, true);

// Initialize JSON file if it doesn't exist
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

// Handle the upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate the upload
        if (!isset($_FILES['wallpaperImage']) || $_FILES['wallpaperImage']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed.');
        }

        $file = $_FILES['wallpaperImage'];
        $category = $_POST['wallpaperCategory'];

        // Validate file type
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Only JPG, PNG, and WebP images are allowed.');
        }

        // Validate file size
        if ($file['size'] > $maxSize) {
            throw new Exception('File size must be less than 10MB.');
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $destination = $uploadDir . $filename;

        // Move the uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Failed to save the uploaded file.');
        }

        // Add to JSON data
        $wallpapers = json_decode(file_get_contents($dataFile), true);
        $wallpapers[] = [
            'filename' => $filename,
            'path' => $destination,
            'category' => $category,
            'upload_date' => date('Y-m-d H:i:s'),
            'downloads' => 0
        ];
        file_put_contents($dataFile, json_encode($wallpapers, JSON_PRETTY_PRINT));

        // Success response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Wallpaper uploaded successfully!',
            'filename' => $filename
        ]);
        exit;

    } catch (Exception $e) {
        // Error response
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}
?>