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
            <li><a href="market.php">Market Place</a></li>
            <li><a href="#aboutUs">about Us</a></li>
        </ul>

        <div class="top-btn">
            <a href="./controller/create_add_btn_function.php" class="btn-2">Publish Your Add</a>        
            <a href="login_register.php" class="btn-1">Login</a>
        </div>
    </header>

    <!-- Home section -->
    <section class="home-section" id="home">  
         <div class="schedule-container">
            <h3 class="headline">Collecting Schedule</h3>
                <div class="calender-container">
                    <div class="calender">calender component area</div>
                    <div class="search_bar">
                        <form class="src_form" action="./controller/search_appointments.php" method="post">
                            <select name="area" id="area">
                                <option value="colombo1">colombo 1</option>
                                <option value="colombo2">colombo 2</option>
                                <option value="colombo3">colombo 3</option>
                                <option value="colombo4">colombo 4</option>
                            </select>
                            <button type="submit" name="btn_search" class="btn-2">Search</button>
                        </form>
                    </div>
                </div>
            </div>    

            <div class="paragraph-container">
                <div class="paragraph">Garbage recycling is crucial for environmental sustainability and resource conservation. By recycling, we reduce the amount of waste sent to landfills, which helps minimize pollution and the release of harmful greenhouse gases. Recycling also conserves natural resources like timber, water, and minerals by reusing materials such as paper, plastic, glass, and metals. This process reduces the need for raw material extraction, which often involves energy-intensive and environmentally damaging practices. Additionally, recycling saves energy, as manufacturing products from recycled materials typically requires less energy than producing them from scratch. By promoting recycling, we can protect ecosystems, reduce our carbon footprint, and move toward a more sustainable future. It is a simple yet powerful way for individuals and communities to contribute to the health of the planet.</div>
                <a href="#" class="btn-2">Join with us</a>
            </div>   
    </section>
    <!-- about us section -->
    <section class="aboutus" id="aboutUs">
        <h1 class="heading">About us</h1>


    </section>

    <footer>

    </footer>

</body>
</html>