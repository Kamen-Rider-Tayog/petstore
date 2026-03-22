<?php
require_once __DIR__ . '/../../backend/functions/helpers.php';

$page_title = 'Login';
$hide_nav_footer = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Login</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Auth CSS -->
    <link rel="stylesheet" href="/Ria-Pet-Store/assets/css/auth/auth.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <a href="<?php echo url(''); ?>" class="logo"><?php echo APP_NAME; ?></a>
        </div>
        
        <div class="auth-card">
            <h1>Login</h1>
            <p class="subtitle">Enter your details to sign in to your account</p>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['registered'])): ?>
                <div class="success-message">Registration successful! Please login.</div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo url('login_process'); ?>">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="forgot-link">
                    <a href="#">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn-login">Login</button>
            </form>
            
            <div class="divider">
                <span>Or sign in with</span>
            </div>
            
            <div class="social-login">
                <a href="#" class="social-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Sign in with Google
                </a>
            </div>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="<?php echo url('register'); ?>">Sign up</a></p>
            </div>
        </div>
        
        <div class="auth-footer-links">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></p>
            <div class="footer-links">
                <a href="<?php echo url('privacy'); ?>">Privacy Policy</a>
                <a href="<?php echo url('terms'); ?>">Terms & Conditions</a>
            </div>
        </div>
    </div>
</body>
</html>