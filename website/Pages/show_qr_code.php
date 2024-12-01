<?php
// Get the file path from the URL parameter
$qr_code_path = isset($_GET['file']) ? urldecode($_GET['file']) : null;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serenity Spa - QR Code de Réservation</title>
    <link rel="stylesheet" href="../Css/Home.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Playfair+Display:wght@400;600&display=swap" rel="stylesheet">
    <style>

        body{
            margin-top: 150px;
        }
        /* Styling for the QR code image */
        .qr-code-img {
            width: 400px; /* Adjust the size as per your design */
            height: 400px;
            display: block;
            margin: 10px auto;
            border: 2px solid #000; /* Optional border for emphasis */
        }

        .content {
            height: 600px;
            text-align: center;
        }

        .note {
            font-size: 1.2em;
            margin-top: 20px;
        }

        .done-button {
            background-color: black; /* Green button */
            color: white;
            font-size: 1.1em;
            padding: 15px 32px;
            text-align: center;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 30px;
            margin-bottom: 100px;
        }

        .done-button:hover {
            background-color: #45a049; /* Darker green on hover */
        }

        footer {
            text-align: center;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="navbar-logo">
                <img src="../Images/logo.png" alt="Logo Serenity Spa">
            </div>
            <ul class="navbar-links">
                <li><a href="#accueil">Accueil</a></li>
                <li><a href="#about">À propos</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#cta">Contact</a></li>
            </ul>
            <a href="./login.html">
                <button class="navbar-button">Prendre rendez-vous</button>
            </a>
        </nav>
    </header>    
    <main>
        <section id="accueil">
            <div class="content">
                <h1>Votre QR Code de Réservation</h1>
                <?php
                if ($qr_code_path && file_exists($qr_code_path)) {
                    // Display the QR code image
                    echo "<img src='" . $qr_code_path . "' alt='QR Code' class='qr-code-img'>";
                } else {
                    echo "<p>QR Code introuvable.</p>";
                }
                ?>
                <p class="note">Scannez ou capturez une capture d'écran de ce QR Code pour confirmer ou afficher vos détails de réservation.</p>
                <a href="dashboard.php">
                    <button class="done-button">DONE</button>
                </a>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-logo">
            <img src="../Images/logo.png" alt="Logo Serenity Spa">
            <p>"We don't keep our beauty secrets"</p>
        </div>
        <div class="footer-contact">
            <h3>Contact</h3>
            <p>123 Rue de la Sérénité</p>
            <p>75000 Paris, France</p>
            <p>Tél : 01 23 45 67 89</p>
            <p>Email : contact@serenityspa.com</p>
        </div>
        <div class="footer-hours">
            <h3>Horaires d'ouverture</h3>
            <p>Lundi - Vendredi : 10h - 20h</p>
            <p>Samedi : 9h - 21h</p>
            <p>Dimanche : 11h - 18h</p>
        </div>
    </footer>
</body>
</html>
