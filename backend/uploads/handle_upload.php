<?php
function uploadImage($file, $targetFolder = '../assets/uploads/pets/') {
    // Check if file was uploaded without errors
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Validate file type (only jpg, png, gif)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = $file['type'];
    
    if (!in_array($fileType, $allowedTypes)) {
        return false;
    }
    
    // Validate file size (max 2MB)
    $maxSize = 2 * 1024 * 1024; // 2MB in bytes
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    // Get file extension
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $extension;
    
    // Create target folder if it doesn't exist
    if (!file_exists($targetFolder)) {
        mkdir($targetFolder, 0777, true);
    }
    
    // Move file to target folder
    $targetPath = $targetFolder . $filename;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $filename;
    }
    
    return false;
}
?>