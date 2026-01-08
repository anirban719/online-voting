<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Ensure animations work */
        .char { display: inline-block; }
    </style>
</head>
<body>
    <!-- Navigation Bar with Animation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm navbar-animated">
        <div class="container">
            <a class="navbar-brand brand-animated" href="index.php">
                <i class="fas fa-vote-yea"></i> Voting System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item nav-item-animated">
                        <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item nav-item-animated">
                        <a class="nav-link" href="user/login.php"><i class="fas fa-sign-in-alt"></i> User Login</a>
                    </li>
                    <li class="nav-item nav-item-animated">
                        <a class="nav-link" href="admin/login.php"><i class="fas fa-user-shield"></i> Admin</a>
                    </li>
                    <li class="nav-item nav-item-animated">
                        <a class="nav-link" href="user/register.php"><i class="fas fa-user-plus"></i> Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
        
        <div class="container">
            <div class="row align-items-center" style="min-height: calc(100vh - 70px);">
                <div class="col-md-6 text-content">
                    <div class="hero-text">
                        <h1 class="fw-bold heading-animated" style="color: blue; font-size: 3.5rem; margin-bottom: 1rem;">
                            <span class="char" style="color: blue;">W</span><span class="char" style="color: blue;">e</span><span class="char" style="color: blue;">l</span><span class="char" style="color: blue;">c</span><span class="char" style="color: blue;">o</span><span class="char" style="color: blue;">m</span><span class="char" style="color: blue;">e</span>
                        </h1>
                       
                        <h2 id="typingText" class="fw-bold typing-animated" style="color: white; font-size: 2.5rem; margin-bottom: 1rem; min-height: 60px;"></h2>
                        <p class="lead mb-4 description-animated" style="color: blue; font-size: 1.1rem;">
                            <i class="fas fa-shield-alt"></i> Secure, transparent, and easy voting platform
                        </p>
                        <div class="button-group button-group-animated">
                            <a href="user/login.php" class="btn btn-primary btn-lg btn-animated">
                                <i class="fas fa-arrow-right"></i> Start Voting
                            </a>
                            <a href="user/register.php" class="btn btn-outline-primary btn-lg btn-animated">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 image-content">
                    <div class="image-wrapper image-float">
                        <img src="assets/images/photo.jpg" alt="Voting" class="img-fluid img-animated">
                        <div class="glow-effect"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <section class="features-section py-5">
        <div class="container">
            <h2 class="text-center mb-5 section-title-animated">Why Choose Us?</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="feature-card feature-card-animated">
                        <div class="feature-icon"><i class="fas fa-lock"></i></div>
                        <h5>Highly Secure</h5>
                        <p>End-to-end encrypted voting system</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card feature-card-animated" style="animation-delay: 0.2s;">
                        <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
                        <h5>Real-time Results</h5>
                        <p>Instant voting results and transparency</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card feature-card-animated" style="animation-delay: 0.4s;">
                        <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
                        <h5>Easy to Use</h5>
                        <p>User-friendly interface for everyone</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="stat-card stat-card-animated">
                        <h3 class="stat-number"><span class="counter" data-target="10000">0</span>+</h3>
                        <p class="stat-label">Users</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="stat-card stat-card-animated" style="animation-delay: 0.2s;">
                        <h3 class="stat-number"><span class="counter" data-target="500">0</span>+</h3>
                        <p class="stat-label">Elections</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="stat-card stat-card-animated" style="animation-delay: 0.4s;">
                        <h3 class="stat-number"><span class="counter" data-target="99">0</span>%</h3>
                        <p class="stat-label">Satisfaction</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-5">
        <div class="container text-center">
            <h2 class="mb-4 cta-title-animated">Ready to Vote?</h2>
            <p class="lead mb-5 cta-description-animated">Join thousands voting securely online</p>
            <div class="cta-buttons">
                <a href="user/register.php" class="btn btn-primary btn-lg btn-animated-cta">
                    <i class="fas fa-check-circle"></i> Register Now
                </a>
                <a href="user/login.php" class="btn btn-outline-primary btn-lg btn-animated-cta" style="animation-delay: 0.1s;">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 footer-animated">
        <div class="container text-center">
            <p>&copy; 2024 Online Voting System. All rights reserved.</p>
            <div class="social-links mt-3">
                <a href="#" class="social-icon"><i class="fab fa-facebook"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-linkedin"></i></a>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top -->
    <button id="scrollToTopBtn" class="scroll-to-top"><i class="fas fa-arrow-up"></i></button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Character animation for "Welcome"
            const chars = document.querySelectorAll('.char');
            chars.forEach((char, idx) => {
                setTimeout(() => {
                    char.classList.add('char-animated');
                }, idx * 50);
            });

            // Typing effect
            setTimeout(typeEffect, 600);

            // Counter animation on scroll
            initCounters();
            initScrollBtn();
        });

        function typeEffect() {
            const text = "To Online Voting System";
            const elem = document.getElementById('typingText');
            if (!elem) return;
            
            let idx = 0;
            elem.textContent = '';
            const timer = setInterval(() => {
                if (idx < text.length) {
                    elem.textContent += text[idx++];
                } else {
                    clearInterval(timer);
                }
            }, 80);
        }

        function initCounters() {
            const obs = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !entry.target.hasAttribute('data-counted')) {
                        entry.target.setAttribute('data-counted', 'true');
                        entry.target.querySelectorAll('.counter').forEach(counter => {
                            animateCounter(counter);
                        });
                    }
                });
            }, {threshold: 0.5});

            const section = document.querySelector('.stats-section');
            if (section) obs.observe(section);
        }

        function animateCounter(el) {
            const target = parseInt(el.getAttribute('data-target'));
            const duration = 2000;
            const start = Date.now();

            function update() {
                const elapsed = Date.now() - start;
                const progress = Math.min(elapsed / duration, 1);
                el.textContent = Math.floor(progress * target);
                if (progress < 1) requestAnimationFrame(update);
            }
            update();
        }

        function initScrollBtn() {
            const btn = document.getElementById('scrollToTopBtn');
            if (!btn) return;

            window.addEventListener('scroll', () => {
                btn.classList.toggle('visible', window.pageYOffset > 300);
            });

            btn.addEventListener('click', () => {
                window.scrollTo({top: 0, behavior: 'smooth'});
            });
        }
    </script>
</body>
</html>
