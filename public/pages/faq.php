<?php
require_once '../../backend/config/database.php';
require_once '../../backend/includes/auth.php';
require_once '../../backend/includes/header.php';

// Get all active FAQs grouped by category
try {
    $stmt = $conn->prepare("
        SELECT category, question, answer, display_order
        FROM faqs
        WHERE is_active = 1
        ORDER BY category, display_order, created_at DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $faqs = $result->fetch_all(MYSQLI_ASSOC);

    // Group FAQs by category
    $faqCategories = [];
    foreach ($faqs as $faq) {
        $faqCategories[$faq['category']][] = $faq;
    }
} catch (Exception $e) {
    $faqCategories = [];
    error_log("FAQ Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frequently Asked Questions - Pet Store</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/faq.css">
</head>
<body>
    <?php include '../../backend/includes/header.php'; ?>

    <!-- FAQ Hero Section -->
    <section class="faq-hero">
        <div class="container">
            <h1>Frequently Asked Questions</h1>
            <p>Find answers to common questions about our services, products, and policies</p>
        </div>
    </section>

    <!-- Search Section -->
    <section class="faq-search">
        <div class="container">
            <div class="search-container">
                <input type="text" id="faqSearch" class="search-input" placeholder="Search FAQs...">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>
    </section>

    <!-- FAQ Content -->
    <section class="faq-content">
        <div class="container">
            <div class="faq-container">
                <?php if (empty($faqCategories)): ?>
                    <div class="no-results">
                        <i class="fas fa-question-circle"></i>
                        <h3>No FAQs Available</h3>
                        <p>We're working on adding frequently asked questions. Please check back later or contact us directly.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($faqCategories as $category => $faqs): ?>
                        <div class="faq-category" data-category="<?php echo htmlspecialchars($category); ?>">
                            <h2 class="category-title"><?php echo htmlspecialchars($category); ?></h2>
                            <div class="faq-accordion">
                                <?php foreach ($faqs as $index => $faq): ?>
                                    <div class="faq-item" data-question="<?php echo htmlspecialchars(strtolower($faq['question'])); ?>">
                                        <button class="faq-question" aria-expanded="false">
                                            <span><?php echo htmlspecialchars($faq['question']); ?></span>
                                            <i class="fas fa-chevron-down faq-icon"></i>
                                        </button>
                                        <div class="faq-answer">
                                            <div class="faq-answer-content">
                                                <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Contact CTA -->
    <section class="contact-cta">
        <div class="container">
            <h2>Still Have Questions?</h2>
            <p>Can't find the answer you're looking for? Our team is here to help!</p>
            <a href="contact.php" class="btn-primary">Contact Us</a>
        </div>
    </section>

    <?php include '../../backend/includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');
            const searchInput = document.getElementById('faqSearch');
            const faqCategories = document.querySelectorAll('.faq-category');

            // Accordion functionality
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');

                question.addEventListener('click', function() {
                    const isActive = item.classList.contains('active');

                    // Close all FAQ items
                    faqItems.forEach(otherItem => {
                        otherItem.classList.remove('active');
                        otherItem.querySelector('.faq-question').setAttribute('aria-expanded', 'false');
                    });

                    // Open clicked item if it wasn't active
                    if (!isActive) {
                        item.classList.add('active');
                        this.setAttribute('aria-expanded', 'true');
                    }
                });
            });

            // Search functionality
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                let hasVisibleItems = false;

                faqCategories.forEach(category => {
                    const categoryItems = category.querySelectorAll('.faq-item');
                    let categoryHasVisibleItems = false;

                    categoryItems.forEach(item => {
                        const question = item.dataset.question;
                        const isVisible = question.includes(searchTerm);

                        item.style.display = isVisible ? 'block' : 'none';
                        if (isVisible) {
                            categoryHasVisibleItems = true;
                            hasVisibleItems = true;
                        }
                    });

                    category.style.display = categoryHasVisibleItems ? 'block' : 'none';
                });

                // Show/hide no results message
                const noResults = document.querySelector('.no-results');
                if (noResults) {
                    noResults.style.display = hasVisibleItems ? 'none' : 'block';
                }
            });

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === '/' && !searchInput.matches(':focus')) {
                    e.preventDefault();
                    searchInput.focus();
                }
            });
        });
    </script>
</body>
</html>