<?php
$page_title = '404 - Page Not Found';
require_once __DIR__ . '/../../backend/includes/header.php';
http_response_code(404);
?>

<div class="error-page">
    <div class="error-code">404</div>
    <h1>Oops! Page Not Found</h1>
    <p>The page you're looking for seems to have wandered off. Don't worry, we'll help you find your way back!</p>

    <div class="error-actions">
        <a href="/petstore/" class="btn btn-primary">Go Home</a>
        <a href="javascript:history.back()" class="btn btn-outline">Go Back</a>
        <a href="/petstore/contact" class="btn btn-outline">Contact Us</a>
    </div>
</div>

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>