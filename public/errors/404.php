<?php
require_once '../../backend/config/database.php';
require_once '../../backend/includes/auth.php';
require_once '../../backend/includes/header.php';

// Set HTTP status code to 404
http_response_code(404);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - Ria Pet Store</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/404.css">
</head>
<body>
    <?php include '../../backend/includes/header.php'; ?>

    <!-- Error Hero Section -->
    <section class="error-hero">
        <div class="error-content">
            <div class="error-code">404</div>
            <h1 class="error-title">Oops! Page Not Found</h1>
            <p class="error-message">
                The page you're looking for seems to have wandered off with one of our playful puppies.
                Don't worry, we'll help you find your way back!
            </p>

            <div class="error-actions">
                <a href="../index.php" class="btn-primary">
                    <i class="fas fa-home"></i> Go Home
                </a>
                <a href="javascript:history.back()" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Go Back
                </a>
                <a href="contact.php" class="btn-secondary">
                    <i class="fas fa-envelope"></i> Contact Us
                </a>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <section class="search-section">
        <div class="container">
            <div class="search-container">
                <h2>Search Our Site</h2>
                <p>Try searching for what you were looking for:</p>

                <form class="search-form" action="search_pets.php" method="GET">
                    <input type="text" name="q" class="search-input" placeholder="Search for pets, products, or services..." required>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>

            <div class="popular-links">
                <h3>Popular Pages</h3>
                <div class="links-grid">
                    <a href="../index.php" class="link-card">
                        <i class="fas fa-home"></i>
                        <h4>Home</h4>
                        <p>Return to our homepage</p>
                    </a>
                    <a href="pets.php" class="link-card">
                        <i class="fas fa-paw"></i>
                        <h4>Available Pets</h4>
                        <p>Browse our adorable pets</p>
                    </a>
                    <a href="products.php" class="link-card">
                        <i class="fas fa-shopping-bag"></i>
                        <h4>Pet Supplies</h4>
                        <p>Shop for pet products</p>
                    </a>
                    <a href="services.php" class="link-card">
                        <i class="fas fa-cut"></i>
                        <h4>Services</h4>
                        <p>Grooming and vet services</p>
                    </a>
                    <a href="faq.php" class="link-card">
                        <i class="fas fa-question-circle"></i>
                        <h4>FAQ</h4>
                        <p>Frequently asked questions</p>
                    </a>
                    <a href="contact.php" class="link-card">
                        <i class="fas fa-envelope"></i>
                        <h4>Contact Us</h4>
                        <p>Get in touch with us</p>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php include '../../backend/includes/footer.php'; ?>

    <script>
        // Add some fun animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to link cards
            const linkCards = document.querySelectorAll('.link-card');
            linkCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px) scale(1.02)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Focus search input if user presses '/'
            document.addEventListener('keydown', function(e) {
                if (e.key === '/' && !document.activeElement.matches('input')) {
                    e.preventDefault();
                    document.querySelector('.search-input').focus();
                }
            });
        });
    </script>
</body>
</html>