<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manthan 2.0 - Event Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-brain"></i> Manthan 2.0
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#events">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light ms-2" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="hero-title">Manthan 2.0</h1>
            <p class="hero-subtitle">KLE Technological University Belagavi</p>
            <p class="lead">Igniting Young Minds Through Innovation & Creativity</p>
            <a href="#register" class="btn btn-warning btn-lg mt-3">
                <i class="fas fa-user-plus"></i> Get Started
            </a>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-4">About Manthan 2.0</h2>
                    <p class="lead">
                        Manthan 2.0 is an inter-school event organized by KLE Technological University 
                        to foster innovation, creativity, and technical skills among young students.
                    </p>
                    <p>
                        The event features multiple competitions including Ideathon and Hackathon, 
                        providing a platform for students to showcase their talents and learn from 
                        experienced mentors.
                    </p>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Why Participate?</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check-circle text-success"></i> Learn from Industry Experts</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success"></i> Certificate of Participation</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success"></i> Exciting Prizes</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success"></i> Networking Opportunities</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success"></i> Skill Development</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Registration Options -->
    <section id="register" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Register Now</h2>
            <div class="row justify-content-center">
                <div class="col-md-4 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-user-graduate fa-3x text-primary mb-3"></i>
                            <h4 class="card-title">Participant</h4>
                            <p class="card-text">Register as a student participant to compete in events.</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="fas fa-check"></i> 8th Standard & Above</li>
                                <li><i class="fas fa-check"></i> Team of 4 Members</li>
                                <li><i class="fas fa-check"></i> Certificate of Participation</li>
                            </ul>
                            <a href="register.php?role=student" class="btn btn-primary mt-3">Register as Participant</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-chalkboard-teacher fa-3x text-success mb-3"></i>
                            <h4 class="card-title">Mentor</h4>
                            <p class="card-text">Guide and mentor student teams during events.</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="fas fa-check"></i> University Students</li>
                                <li><i class="fas fa-check"></i> 1st to 7th Semester</li>
                                <li><i class="fas fa-check"></i> Leadership Experience</li>
                            </ul>
                            <a href="register.php?role=mentor" class="btn btn-success mt-3">Register as Mentor</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-user-shield fa-3x text-warning mb-3"></i>
                            <h4 class="card-title">Admin Login</h4>
                            <p class="card-text">System administrator access (Only one admin allowed).</p>
                            <div class="alert alert-warning mt-3">
                                <small><i class="fas fa-info-circle"></i> Admin account is already created.</small>
                            </div>
                            <a href="login.php" class="btn btn-warning mt-2">Login as Admin</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Events Section -->
    <section id="events" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Events</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-info text-white">
                            <h4 class="mb-0">Ideathon</h4>
                        </div>
                        <div class="card-body">
                            <p><strong>Date:</strong> 6th December 2026</p>
                            <p><strong>Venue:</strong> KLE Technological University, Belagavi</p>
                            <p><strong>Reporting Time:</strong> 10:00 AM</p>
                            <p><strong>Description:</strong> Present innovative ideas to solve real-world problems.</p>
                            <div class="mt-3">
                                <span class="badge bg-primary">Innovation</span>
                                <span class="badge bg-success">Presentation</span>
                                <span class="badge bg-warning">Team Event</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-warning text-dark">
                            <h4 class="mb-0">Hackathon</h4>
                        </div>
                        <div class="card-body">
                            <p><strong>Date:</strong> 3rd January 2027</p>
                            <p><strong>Venue:</strong> KLE Technological University, Belagavi</p>
                            <p><strong>Reporting Time:</strong> 10:00 AM</p>
                            <p><strong>Description:</strong> Build innovative solutions within 24 hours.</p>
                            <div class="mt-3">
                                <span class="badge bg-primary">Coding</span>
                                <span class="badge bg-success">Development</span>
                                <span class="badge bg-danger">24 Hours</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Contact Us</h2>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5>KLE Technological University</h5>
                            <p class="mb-1"><i class="fas fa-map-marker-alt"></i> Udyambag, Belagavi, Karnataka 590008</p>
                            <p class="mb-1"><i class="fas fa-phone"></i> +91-831-2494900</p>
                            <p class="mb-1"><i class="fas fa-envelope"></i> info@kletech.ac.in</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5>Event Coordinators</h5>
                            <p class="mb-1">Prof. Santosh Pattar - 9876543210</p>
                            <p class="mb-1">Prof. Amey Muchandi - 9876543211</p>
                            <p class="mb-1">Email: manthan@kletech.ac.in</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Manthan 2.0</h5>
                    <p>KLE Technological University Belagavi</p>
                </div>
                <div class="col-md-6 text-end">
                    <p>&copy; <?php echo date('Y'); ?> Manthan 2.0. All rights reserved.</p>
                    <p>
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>