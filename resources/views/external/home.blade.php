{{-- File: resources/views/external/home.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elegant Jewelry - Premium Gold & Silver Jewelry Australia</title>
    <meta name="description" content="Discover Australia's finest collection of handcrafted gold and silver jewelry. Premium quality, competitive prices, and exceptional customer service.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --gold-primary: #D4AF37;
            --gold-secondary: #B8860B;
            --dark-primary: #1a1a1a;
            --dark-secondary: #2d2d2d;
            --light-gray: #f8f9fa;
            --medium-gray: #6c757d;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .font-playfair {
            font-family: 'Playfair Display', serif;
        }

        /* Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.8rem;
            color: var(--gold-primary) !important;
        }

        .nav-link {
            font-weight: 500;
            color: var(--dark-primary) !important;
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--gold-primary) !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--gold-primary);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 700;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 1.5rem;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: rgba(255,255,255,0.9);
            margin-bottom: 2rem;
        }

        .btn-gold {
            background: var(--gold-primary);
            border: 2px solid var(--gold-primary);
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .btn-gold:hover {
            background: transparent;
            color: var(--gold-primary);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(212, 175, 55, 0.3);
        }

        /* Features Section */
        .features-section {
            padding: 100px 0;
            background: var(--light-gray);
        }

        .feature-card {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--gold-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2rem;
        }

        /* Products Section */
        .products-section {
            padding: 100px 0;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .product-image {
            height: 250px;
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--medium-gray);
        }

        /* About Section */
        .about-section {
            padding: 100px 0;
            background: var(--dark-primary);
            color: white;
        }

        .about-stats {
            display: flex;
            justify-content: space-around;
            margin-top: 50px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--gold-primary);
            font-family: 'Playfair Display', serif;
        }

        /* Footer */
        .footer {
            background: var(--dark-primary);
            color: white;
            padding: 60px 0 30px;
        }

        .footer-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: var(--gold-primary);
            margin-bottom: 15px;
        }

        .footer-link {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-link:hover {
            color: var(--gold-primary);
        }

        .social-links a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: var(--gold-primary);
            color: white;
            text-align: center;
            line-height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: var(--gold-secondary);
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .about-stats {
                flex-direction: column;
                gap: 30px;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.8s ease forwards;
        }

        /* Loading Animation */
        .loading-animation {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }

        .loading-animation.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <i class="fas fa-gem me-2"></i>Elegant Jewelry
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#products">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title loading-animation">
                            Timeless Elegance
                            <br>
                            <span style="color: var(--gold-primary);">Crafted Perfect</span>
                        </h1>
                        <p class="hero-subtitle loading-animation">
                            Discover Australia's finest collection of handcrafted gold and silver jewelry. 
                            Each piece tells a story of artistry, tradition, and uncompromising quality.
                        </p>
                        <div class="loading-animation">
                            <a href="#products" class="btn btn-gold btn-lg me-3">
                                <i class="fas fa-shopping-bag me-2"></i>Shop Collection
                            </a>
                            <a href="#about" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-play me-2"></i>Learn More
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="loading-animation">
                        <i class="fas fa-gem fa-15x" style="color: var(--gold-primary); filter: drop-shadow(0 0 30px rgba(212, 175, 55, 0.5));"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="services" class="features-section">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="font-playfair mb-4 loading-animation" style="font-size: 2.5rem; color: var(--dark-primary);">
                        Why Choose Elegant Jewelry?
                    </h2>
                    <p class="lead text-muted loading-animation">
                        We combine traditional craftsmanship with modern technology to create jewelry that exceeds expectations.
                    </p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card loading-animation">
                        <div class="feature-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h4 class="font-playfair mb-3">Certified Quality</h4>
                        <p class="text-muted">
                            All our jewelry is certified with hallmarks ensuring authentic precious metals and genuine gemstones.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card loading-animation">
                        <div class="feature-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h4 class="font-playfair mb-3">Expert Craftsmanship</h4>
                        <p class="text-muted">
                            Our master jewelers bring decades of experience to create pieces that stand the test of time.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card loading-animation">
                        <div class="feature-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h4 class="font-playfair mb-3">Express Delivery</h4>
                        <p class="text-muted">
                            Fast and secure shipping across Australia with full insurance and premium packaging.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card loading-animation">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4 class="font-playfair mb-3">Lifetime Warranty</h4>
                        <p class="text-muted">
                            Comprehensive warranty covering craftsmanship and materials for complete peace of mind.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card loading-animation">
                        <div class="feature-icon">
                            <i class="fas fa-gem"></i>
                        </div>
                        <h4 class="font-playfair mb-3">Custom Design</h4>
                        <p class="text-muted">
                            Bring your vision to life with our custom jewelry design service and personal consultation.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card loading-animation">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4 class="font-playfair mb-3">24/7 Support</h4>
                        <p class="text-muted">
                            Professional consultation and customer service available around the clock for your convenience.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="products-section">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="font-playfair mb-4 loading-animation" style="font-size: 2.5rem; color: var(--dark-primary);">
                        Our Collections
                    </h2>
                    <p class="lead text-muted loading-animation">
                        From engagement rings to statement necklaces, discover jewelry that celebrates life's precious moments.
                    </p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="product-card loading-animation">
                        <div class="product-image">
                            <i class="fas fa-ring"></i>
                        </div>
                        <div class="p-4">
                            <h5 class="font-playfair mb-2">Luxury Watches</h5>
                            <p class="text-muted mb-3">Precision timepieces crafted with precious metals and fine complications.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h6 mb-0" style="color: var(--gold-primary);">From $1,850</span>
                                <a href="#" class="btn btn-outline-primary btn-sm">View Collection</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="loading-animation">
                        <h2 class="font-playfair mb-4" style="font-size: 2.5rem; color: white;">
                            Three Generations of Excellence
                        </h2>
                        <p class="mb-4" style="color: rgba(255,255,255,0.9);">
                            Since 2006, Our Jewelry has been Australia's premier destination for fine jewelry. 
                            Founded, our family business has grown from a small 
                            workshop in Melbourne to a nationally recognized brand, while maintaining our commitment 
                            to exceptional craftsmanship and personalized service.
                        </p>
                        <p class="mb-4" style="color: rgba(255,255,255,0.9);">
                            Our master craftsmen combine time-honored techniques with modern precision tools to create 
                            pieces that capture both contemporary style and timeless elegance. Every piece in our 
                            collection is designed to become a treasured heirloom, passed down through generations.
                        </p>
                        <a href="#contact" class="btn btn-gold btn-lg">
                            <i class="fas fa-phone me-2"></i>Schedule Consultation
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="loading-animation">
                        <div class="about-stats">
                            <div class="stat-item">
                                <div class="stat-number">19+</div>
                                <div>Years of Excellence</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">10K+</div>
                                <div>Happy Customers</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">100+</div>
                                <div>Unique Designs</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5" style="background: var(--light-gray);">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="font-playfair mb-4 loading-animation" style="font-size: 2.5rem; color: var(--dark-primary);">
                        What Our Customers Say
                    </h2>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow loading-animation h-100">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="mb-4 fst-italic">
                                "The quality of craftsmanship is exceptional. My engagement ring is absolutely stunning 
                                and the service was personalized and professional throughout the entire process."
                            </p>
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="bg-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">xxxx xxxx</h6>
                                    <small class="text-muted">Sydney, NSW</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card border-0 shadow loading-animation h-100">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="mb-4 fst-italic">
                                "Our Jewelry created custom wedding bands that exceeded our expectations. 
                                The attention to detail and customer service is unmatched anywhere in Australia."
                            </p>
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="bg-success rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Michael & Emma</h6>
                                    <small class="text-muted">Melbourne, VIC</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card border-0 shadow loading-animation h-100">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="mb-4 fst-italic">
                                "I've been a customer for over 10 years. The quality, craftsmanship, and value 
                                is consistently outstanding. Highly recommend for any special occasion jewelry."
                            </p>
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="bg-info rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">David Thompson</h6>
                                    <small class="text-muted">Brisbane, QLD</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5" style="background: white;">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="font-playfair mb-4 loading-animation" style="font-size: 2.5rem; color: var(--dark-primary);">
                        Visit Our Showroom
                    </h2>
                    <p class="lead text-muted loading-animation">
                        Experience our jewelry collection in person at our flagship showroom in the heart of Melbourne.
                    </p>
                </div>
            </div>
            
            <div class="row g-5">
                <div class="col-lg-6">
                    <div class="loading-animation">
                        <h4 class="font-playfair mb-4">Get In Touch</h4>
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="fas fa-map-marker-alt text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Address</h6>
                                        <p class="mb-0 text-muted">123 Collins Street, Melbourne VIC 3000</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="fas fa-phone text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Phone</h6>
                                        <p class="mb-0 text-muted">+61 3 9123 4567</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <div class="bg-info rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="fas fa-envelope text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Email</h6>
                                        <p class="mb-0 text-muted">info@elegantjewelry.com.au</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="fas fa-clock text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Hours</h6>
                                        <p class="mb-0 text-muted">Mon-Sat: 9:00 AM - 6:00 PM<br>Sun: 11:00 AM - 4:00 PM</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="loading-animation">
                        <h4 class="font-playfair mb-4">Send Us a Message</h4>
                        <form>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="firstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="firstName" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="lastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" required>
                                </div>
                                <div class="col-12">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                                <div class="col-12">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone">
                                </div>
                                <div class="col-12">
                                    <label for="subject" class="form-label">Subject</label>
                                    <select class="form-select" id="subject" required>
                                        <option value="">Choose...</option>
                                        <option value="consultation">Schedule Consultation</option>
                                        <option value="custom">Custom Design Inquiry</option>
                                        <option value="repair">Repair Services</option>
                                        <option value="general">General Question</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" rows="4" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-gold btn-lg w-100">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="footer-brand font-playfair">
                        <i class="fas fa-gem me-2"></i>Our Jewelry
                    </div>
                    <p class="text-muted mb-4">
                        Australia's premier destination for fine jewelry since 2006. 
                        Crafting timeless pieces with exceptional quality and artistry.
                    </p>
                    <div class="social-links">
                        <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" title="Pinterest"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3" style="color: var(--gold-primary);">Collections</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="footer-link">Engagement Rings</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Wedding Bands</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Necklaces</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Earrings</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Bracelets</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Watches</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3" style="color: var(--gold-primary);">Services</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="footer-link">Custom Design</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Jewelry Repair</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Appraisals</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Ring Sizing</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Cleaning</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Consultations</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3" style="color: var(--gold-primary);">Support</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="footer-link">Help Center</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Size Guide</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Care Instructions</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Warranty</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Returns</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Shipping</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3" style="color: var(--gold-primary);">Company</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="footer-link">About Us</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Our Story</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Careers</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Press</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Privacy Policy</a></li>
                        <li class="mb-2"><a href="#" class="footer-link">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4" style="border-color: rgba(255,255,255,0.2);">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        &copy; 2024 Jewelry. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-muted">
                        Made with <i class="fas fa-heart text-danger"></i> in Australia
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scrolling for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Intersection Observer for animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, observerOptions);

            // Observe all elements with loading-animation class
            document.querySelectorAll('.loading-animation').forEach(el => {
                observer.observe(el);
            });

            // Navbar scroll effect
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                    navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                    navbar.style.boxShadow = '0 2px 20px rgba(0,0,0,0.15)';
                } else {
                    navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                    navbar.style.boxShadow = '0 2px 20px rgba(0,0,0,0.1)';
                }
            });

            // Contact form submission
            document.querySelector('#contact form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Simulate form submission
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
                submitBtn.disabled = true;
                
                setTimeout(() => {
                    submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Message Sent!';
                    submitBtn.classList.remove('btn-gold');
                    submitBtn.classList.add('btn-success');
                    
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.classList.remove('btn-success');
                        submitBtn.classList.add('btn-gold');
                        submitBtn.disabled = false;
                        this.reset();
                    }, 2000);
                }, 1500);
            });

            // Add loading animations trigger
            setTimeout(() => {
                document.querySelectorAll('.loading-animation').forEach((el, index) => {
                    setTimeout(() => {
                        el.classList.add('visible');
                    }, index * 100);
                });
            }, 100);
        });
    </script>
</body>
</html>