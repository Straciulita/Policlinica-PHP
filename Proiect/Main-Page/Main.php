<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page-Sanavita</title>

    <link rel="stylesheet" href="style.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <!--Navigation Bar-->

        <!--Logo name-->
        <a href="Main.php" class="logo">
            <img src="./Resurse/logo.png" alt="Policlinica Sanavita Logo" class="logo-img">
        </a>


        <!--Navigation links-->
        <nav class="navbar">
            <ul>
                <li><a href="Main.php">Home</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="Appointment.php">Appointment</a></li>
                <li><a href="Contact.html">Contact</a></li>
                <?php if (isset($_SESSION['username'])): ?>
                    <li><a href="Istoric.php"><i class="fas fa-history"></i> Istoric</a></li>
                <?php else: ?>
                    <li><a href="/Proiect/LoginForm/index.php">Login</a></li>
                    <li><a href="/Proiect/LoginForm/Singin.html">Register</a></li>
                <?php endif; ?>
            </ul>

        </nav>

        <div class="user-info">
            <?php if (isset($_SESSION['username'])): ?>
                <span class="navbar-user"><i class="fas fa-user-circle"></i> Bun venit, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php endif; ?>
        </div>
        
    </header>

    <!--Main content-->
    <section id="home" class="home">
        <div class="row">
            <div class="images">
                <img src="./Resurse/doctors.png" alt="Doctor Image" class="doctor-img">
            </div>

            <div class="content">
                <h1>Welcome to Sanavita Clinic</h1>
                <p>Your health is our priority. We offer a wide range of medical services to ensure your well-being.</p>
                <a href="Appointment.php"><button class="button">Book an Appointment</button></a>
            </div>   
        </div>
    </section>
   
    <footer class="footer">
        <p>&copy; 2025 Sanavita Clinic. All rights reserved.</p>
    </footer>

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    

</body>
</html>