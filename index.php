<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>home page</title>

    <!-- css links -->
     <link rel="stylesheet" href="./asset/css/main.css">
     <link rel="stylesheet" href="./asset/css/components.css">
     <link rel="stylesheet" href="./asset/css/home_page.css">

</head>
<body>

    <header>
        <a href="#home" class="logo">Welcome to <span>RecyclOX</span></a>

        <ul class="navbar">
            <li><a href="#home">Home</a></li>
            <li><a href="#aboutUs">about Us</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="market.php">Market Place</a></li>
        </ul>

        <div class="top-btn">
            <a href="./controller/create_add_btn_function.php" class="btn-2">Publish Your Add</a>        
            <a href="login_register.php" class="btn-1">Login</a>
        </div>
    </header>

    <!-- Home section -->
    <section class="home-section" id="home">  
        <div class="paragraph-container">
            <h2>Your one-stop platform for buying, selling, and managing garbage collection schedules.</h2>
            <a href="./market.php" class="btn-2">Join with us</a>
        </div>   

        <div class="calendar-section">
            <h3>Garbage Collection Schedule</h3>
            <div id="calendar"></div>
            <form>
                <select id="district" name="district">
                    <option value="">Select District</option>
                    <option value="district1">District 1</option>
                    <option value="district2">District 2</option>
                </select>
                <select id="city" name="city">
                    <option value="">Select City</option>
                    <option value="city1">City 1</option>
                    <option value="city2">City 2</option>
                </select>
            </form>
        </div>
    </section>

    <!-- about us section -->
    <section class="aboutus" id="aboutUs">
    <h1 class="heading">About Us</h1>
    <p class="subheading">Empowering communities with sustainable waste management solutions.</p>

    <div class="feature">
        <i class="fas fa-recycle"></i>
        <h2>Buy & Sell Garbage</h2>
        <p>Our platform connects individuals and businesses looking to trade recyclable materials. Verified buyers and sellers ensure a secure and reliable exchange process, reducing waste and promoting sustainability.</p>
    </div>

    <div class="feature">
        <i class="fas fa-calendar-alt"></i>
        <h2>Collection Schedules</h2>
        <p>Stay updated with garbage collection schedules in your area. Our automated notifications and reminders help you never miss a collection day, making waste disposal hassle-free.</p>
    </div>

    <div class="feature">
        <i class="fas fa-leaf"></i>
        <h2>Eco-Friendly</h2>
        <p>We encourage responsible waste management by promoting recycling initiatives. Every small effort contributes to reducing pollution and preserving natural resources for future generations.</p>
    </div>
    
    <div class="cta">
        <p>Be a part of the change. Join us today and contribute to a cleaner, greener future!</p>
        <a href="login_register.php" class="btn-1">Login</a>
    </div>
</section>


    <!-- Services Section -->
<section class="aboutus" id="services">
    <h1 class="heading">Our Services</h1>
    <p class="subheading">Dedicated to enhancing waste management practices and fostering a greener future.</p>

    <div class="service">
        <i class="fas fa-recycle"></i>
        <h2>Buy & Sell Garbage</h2>
        <p>Our platform allows individuals and businesses to easily trade recyclable materials. You can connect with verified buyers and sellers, promoting sustainable practices while reducing waste.</p>
    </div>

    <div class="service">
        <i class="fas fa-calendar-alt"></i>
        <h2>Collection Schedules by Municipal Councils</h2>
        <p>Stay on top of your local garbage collection dates with our municipal council-powered schedule tracker. Receive timely reminders so you never miss a collection day, ensuring efficient waste management.</p>
    </div>

    <div class="service">
        <i class="fas fa-leaf"></i>
        <h2>Eco-Friendly Practices</h2>
        <p>Join our efforts in reducing environmental impact by participating in recycling and proper waste disposal. Our platform helps promote a cleaner, greener environment for future generations.</p>
    </div>

</section>


    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#aboutUs">About</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#home">Schedule</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Email: info@recyclox.com</p>
                <p>Phone: +123 456 7890</p>
                <p>Address: 123, RecyclOx Street, Bambalapitiya</p>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 RecyclOX. All rights reserved.</p>
        </div>
    </footer>

    <script src="./asset/js/home_page.js"></script>

</body>
</html>