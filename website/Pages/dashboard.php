<?php
// Supabase URL and Key (replace with your own details)
// Supabase API URL and Key (replace these with your actual Supabase URL and Key)
$supabase_url = "https://ulxorjunckbvzwchthyc.supabase.co";
$supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InVseG9yanVuY2tidnp3Y2h0aHljIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTczMzA3ODgyNywiZXhwIjoyMDQ4NjU0ODI3fQ.r2WEhkouIq8ktFRRHTVrColYAIEOYc-aonhk-f2Vm6A"; // Service Role or anon key

// Function to fetch data using cURL
function fetchData($endpoint)
{
    global $supabase_url, $supabase_key;

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $supabase_url . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $supabase_key,
        'Content-Type: application/json',
        'apikey: ' . $supabase_key // Same header as your working example
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

    // Execute the request to fetch service data
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get the response status code

    // Check for cURL errors
    if (curl_errno($ch)) {
        // Log the error and return false
        error_log("cURL error: " . curl_error($ch));
        curl_close($ch);
        return false;
    }

    // Close cURL connection
    curl_close($ch);

    // Decode the response
    $data = json_decode($response, true);

    // Check if response is valid
    if (!$data || isset($data['message'])) {
        // Log the error if the response is invalid
        error_log("Error fetching services: " . print_r($data, true));
        return false;
    }

    return $data;
}

// Fetch Services
$services = fetchData('/rest/v1/service');

// Check if the data is returned properly
if (!$services) {
    // Handle errors if services data is not found
    $services = [];
}

// Define a mapping of service names to image paths
$image_mapping = [
    'Facial Spa Treatment' => '../Images/facial.jpg',
    'Back Massage' => '../Images/back-massage.jpg',
    'Foot Massage' => '../Images/foot-massage.jpg',
    'Aromatherapy Massage' => '../Images/thai-massage.png', // Add more mappings as needed
    'Hot Stone Therapy' => '../Images/hot-stone.jpg', // Example
    // Add more services here as needed
];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Serenity Spa</title>
    <link rel="stylesheet" href="../Css/Home.css">
    <link rel="stylesheet" href="../Css/dashboard.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Playfair+Display:wght@400;600&display=swap"
        rel="stylesheet">
</head>

<body>
    <header>
        <nav class="navbar">
            <div class="navbar-logo">
                <img src="../Images/logo.png" alt="Logo Serenity Spa">
            </div>
            <ul class="navbar-links">
                <li><a href="#dashboard">Dashboard</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#cta">Contact</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section id="dashboard">
            <!-- Services Offered -->
            <section id="services" class="colored-section">
                <h2>Nos Services</h2>
                <div class="services-container">
                    <?php if (!empty($services)) { ?>
                        <?php foreach ($services as $service) {
                            // Get the image path based on the service name
                            $service_name = $service['nom'];
                            $image_path = isset($image_mapping[$service_name]) ? $image_mapping[$service_name] : '../Images/facial.jpg'; // Default image if not found
                            ?>
                            <div class="service">
                                <!-- Display Service Image -->
                                <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($service_name); ?>">
                                <h3><?php echo htmlspecialchars($service_name); ?></h3>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($service['description']); ?></p>
                                <p><strong>Durée:</strong> <?php echo htmlspecialchars($service['durée']); ?></p>
                                <p><strong>Prix:</strong> €<?php echo number_format($service['prix'], 2); ?></p>
                                <a href="../Pages/reservation.php?service_id=<?php echo $service['id_service']; ?>">
                                    <button>Réserver maintenant</button>
                                </a>

                            </div>

                        <?php } ?>
                    <?php } else { ?>
                        <p>Aucun service disponible pour le moment.</p>
                    <?php } ?>
                </div>
            </section>

            <!-- Logout Button -->
            <div class="logout">
                <a href="../server/logout.php">
                    <button type="button">Logout</button>
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
    </footer>
</body>

</html>