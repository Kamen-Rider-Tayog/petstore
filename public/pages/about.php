<?php
require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

// Get team members from database
$team_members = [];

try {
    // Check if employees table exists and get team members
    $result = $conn->query("SHOW TABLES LIKE 'employees'");
    
    if ($result->num_rows > 0) {
        // Get active employees with positions to display as team members
        $sql = "SELECT 
                    CONCAT(first_name, ' ', last_name) as name,
                    position,
                    notes as bio,
                    email,
                    'team-placeholder.jpg' as image
                FROM employees 
                WHERE position IS NOT NULL 
                ORDER BY id ASC";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $team_members[] = $row;
            }
        }
    }
} catch (Exception $e) {
    // Log error in development mode
    if (Config::isDebug()) {
        error_log('Error fetching team members: ' . $e->getMessage());
    }
}

// If no team members found from database, use default team members
if (empty($team_members)) {
    $team_members = [
        [
            'name' => 'Sarah Johnson',
            'position' => 'Founder & CEO',
            'bio' => 'With over 15 years in veterinary medicine, Sarah founded PetStore to provide quality pet care products and services.',
            'image' => 'team-sarah.jpg',
            'email' => 'sarah@petstore.com'
        ],
        [
            'name' => 'Mike Chen',
            'position' => 'Head Veterinarian',
            'bio' => 'Dr. Chen specializes in small animal medicine and has been with us since our first year of operation.',
            'image' => 'team-mike.jpg',
            'email' => 'mike@petstore.com'
        ],
        [
            'name' => 'Emily Rodriguez',
            'position' => 'Pet Care Specialist',
            'bio' => 'Emily is passionate about pet nutrition and behavior training. She helps customers choose the best products for their pets.',
            'image' => 'team-emily.jpg',
            'email' => 'emily@petstore.com'
        ]
    ];
}

// Get store statistics
$stats = [];

try {
    // Get total available pets
    $result = $conn->query("SELECT COUNT(*) as total FROM pets WHERE status = 'available'");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stats['pets'] = $row['total'];
    } else {
        $stats['pets'] = 0;
    }

    // Get total customers
    $result = $conn->query("SELECT COUNT(*) as total FROM customers");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stats['customers'] = $row['total'];
    } else {
        $stats['customers'] = 0;
    }

    // Get total products
    $result = $conn->query("SELECT COUNT(*) as total FROM products");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stats['products'] = $row['total'];
    } else {
        $stats['products'] = 0;
    }

    // Get years in business (assuming store opened in 2010)
    $stats['years'] = date('Y') - 2010;

} catch (Exception $e) {
    // Default stats if database queries fail
    if (Config::isDebug()) {
        error_log('Error fetching stats: ' . $e->getMessage());
    }
    
    $stats = [
        'pets' => 500,
        'customers' => 2500,
        'products' => 1000,
        'years' => date('Y') - 2010
    ];
}

$page_title = "About Us - " . APP_NAME;
include '../../backend/includes/header.php';
?>

<link rel="stylesheet" href="<?php echo asset('css/about.css'); ?>">

<div class="about-page">
    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <div class="hero-content">
                <h1>About <?php echo APP_NAME; ?></h1>
                <p class="hero-subtitle">Your trusted partner in pet care since 2010</p>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($stats['pets']); ?>+</span>
                        <span class="stat-label">Happy Pets</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($stats['customers']); ?>+</span>
                        <span class="stat-label">Satisfied Customers</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($stats['products']); ?>+</span>
                        <span class="stat-label">Quality Products</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $stats['years']; ?>+</span>
                        <span class="stat-label">Years of Service</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="mission-section">
        <div class="container">
            <div class="mission-content">
                <h2>Our Mission</h2>
                <p class="mission-text">
                    At <?php echo APP_NAME; ?>, our mission is to enhance the lives of pets and their owners by providing
                    exceptional products, expert advice, and compassionate care. We believe that every pet
                    deserves to live a happy, healthy life, and we're committed to making that possible
                    through our comprehensive range of services and products.
                </p>
                <div class="mission-values">
                    <div class="value-item">
                        <div class="value-icon"><?php echo icon('heart', 32); ?></div>
                        <h3>Compassion</h3>
                        <p>We treat every pet with the love and care they deserve.</p>
                    </div>
                    <div class="value-item">
                        <div class="value-icon"><?php echo icon('star', 32); ?></div>
                        <h3>Quality</h3>
                        <p>We only offer products that meet our high standards of excellence.</p>
                    </div>
                    <div class="value-item">
                        <div class="value-icon"><?php echo icon('user', 32); ?></div>
                        <h3>Trust</h3>
                        <p>Building lasting relationships with our customers is our top priority.</p>
                    </div>
                    <div class="value-item">
                        <div class="value-icon"><?php echo icon('sun', 32); ?></div>
                        <h3>Sustainability</h3>
                        <p>We're committed to environmentally friendly practices and products.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- History Section -->
    <section class="history-section">
        <div class="container">
            <div class="history-content">
                <h2>Our Story</h2>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-date">2010</div>
                        <div class="timeline-content">
                            <h3>The Beginning</h3>
                            <p>
                                <?php echo APP_NAME; ?> was founded by Sarah Johnson, a passionate veterinarian who saw the need
                                for a pet store that truly cared about animal welfare. Starting with just a small
                                storefront, we began our journey to become the premier destination for pet care.
                            </p>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-date">2012</div>
                        <div class="timeline-content">
                            <h3>Growth & Expansion</h3>
                            <p>
                                As our reputation grew, we expanded our product line to include premium pet food,
                                grooming supplies, and veterinary services. Our commitment to quality and customer
                                service helped us establish a loyal customer base.
                            </p>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-date">2015</div>
                        <div class="timeline-content">
                            <h3>Online Presence</h3>
                            <p>
                                Recognizing the importance of digital accessibility, we launched our e-commerce
                                platform. This allowed pet owners from all areas to access our products and
                                expert advice online.
                            </p>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-date">2018</div>
                        <div class="timeline-content">
                            <h3>Community Involvement</h3>
                            <p>
                                We expanded our services to include pet adoption events, community education
                                programs, and partnerships with local animal shelters. Giving back to the
                                community became an integral part of our mission.
                            </p>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-date">2021</div>
                        <div class="timeline-content">
                            <h3>Modernization</h3>
                            <p>
                                Embracing technology, we modernized our operations with advanced inventory
                                management, online booking systems, and enhanced customer service platforms.
                                Our commitment to innovation continues to drive our success.
                            </p>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-date">2024</div>
                        <div class="timeline-content">
                            <h3>Future Forward</h3>
                            <p>
                                Today, <?php echo APP_NAME; ?> stands as a leader in pet care, combining traditional values
                                with modern technology. We're excited about the future and our continued
                                commitment to pets and their families.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <div class="team-content">
                <h2>Meet Our Team</h2>
                <p class="team-intro">
                    Our dedicated team of pet care professionals is passionate about helping you and your
                    furry friends. Each member brings unique expertise and a love for animals to everything
                    they do.
                </p>

                <div class="team-grid">
                    <?php foreach ($team_members as $member): ?>
                    <div class="team-member">
                        <div class="member-image">
                            <img src="<?php echo asset('images/' . htmlspecialchars($member['image'] ?? 'team-placeholder.jpg')); ?>"
                                 alt="<?php echo htmlspecialchars($member['name']); ?>"
                                 onerror="this.onerror=null; this.src='<?php echo asset('images/team-placeholder.jpg'); ?>'">
                        </div>
                        <div class="member-info">
                            <h3><?php echo htmlspecialchars($member['name']); ?></h3>
                            <p class="member-position"><?php echo htmlspecialchars($member['position']); ?></p>
                            <p class="member-bio"><?php echo htmlspecialchars($member['bio']); ?></p>
                            <?php if (isset($member['email'])): ?>
                            <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" class="member-email">
                                <?php echo icon('mail', 14); ?> <?php echo htmlspecialchars($member['email']); ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us">
        <div class="container">
            <div class="why-content">
                <h2>Why Choose <?php echo APP_NAME; ?>?</h2>
                <div class="reasons-grid">
                    <div class="reason-item">
                        <div class="reason-icon"><?php echo icon('user', 32); ?></div>
                        <h3>Expert Knowledge</h3>
                        <p>
                            Our team includes certified veterinarians and pet care specialists
                            who provide expert advice on all aspects of pet health and wellness.
                        </p>
                    </div>

                    <div class="reason-item">
                        <div class="reason-icon"><?php echo icon('package', 32); ?></div>
                        <h3>Quality Products</h3>
                        <p>
                            We carefully select only the highest quality products from trusted
                            manufacturers, ensuring your pet gets the best nutrition and care.
                        </p>
                    </div>

                    <div class="reason-item">
                        <div class="reason-icon"><?php echo icon('truck', 32); ?></div>
                        <h3>Fast Delivery</h3>
                        <p>
                            Enjoy fast, reliable shipping on all orders. Most items ship within
                            24 hours, and we offer express delivery options.
                        </p>
                    </div>

                    <div class="reason-item">
                        <div class="reason-icon"><?php echo icon('message', 32); ?></div>
                        <h3>24/7 Support</h3>
                        <p>
                            Our customer service team is available around the clock to answer
                            your questions and provide assistance whenever you need it.
                        </p>
                    </div>

                    <div class="reason-item">
                        <div class="reason-icon"><?php echo icon('marker', 32); ?></div>
                        <h3>Local Store</h3>
                        <p>
                            Visit our physical store for personalized service, grooming appointments,
                            and the chance to meet our team in person.
                        </p>
                    </div>

                    <div class="reason-item">
                        <div class="reason-icon"><?php echo icon('heart', 32); ?></div>
                        <h3>Community Focus</h3>
                        <p>
                            We're proud to support local animal shelters and community events,
                            making a positive impact in our community.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="about-cta">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Give Your Pet the Best?</h2>
                <p>Join thousands of happy pet owners who trust <?php echo APP_NAME; ?> for their pet care needs.</p>
                <div class="cta-buttons">
                    <a href="<?php echo url('pets'); ?>" class="btn btn-primary">
                        <?php echo icon('paw', 18); ?> Shop Now
                    </a>
                    <a href="<?php echo url('contact'); ?>" class="btn btn-secondary">
                        <?php echo icon('message', 18); ?> Contact Us
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<?php 
include '../../backend/includes/footer.php'; 

// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>