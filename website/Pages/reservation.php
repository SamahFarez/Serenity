<?php
// Supabase credentials
$supabase_url = "https://ulxorjunckbvzwchthyc.supabase.co";
$supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InVseG9yanVuY2tidnp3Y2h0aHljIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTczMzA3ODgyNywiZXhwIjoyMDQ4NjU0ODI3fQ.r2WEhkouIq8ktFRRHTVrColYAIEOYc-aonhk-f2Vm6A"; // Service Role or anon key

// Function to fetch data from Supabase
function fetch_data_from_supabase($endpoint) {
    global $supabase_url, $supabase_key;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $supabase_url . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $supabase_key,
        'Content-Type: application/json',
        'apikey: ' . $supabase_key
    ]);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo "Error: " . curl_error($ch);
        exit();
    }
    
    curl_close($ch);
    
    return json_decode($response, true); // Return the decoded JSON response
}

// Get service ID from the URL
$service_id = isset($_GET['service_id']) ? $_GET['service_id'] : null;

$services = fetch_data_from_supabase('/rest/v1/service');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation</title>
    <link rel="stylesheet" href="../Css/reservation.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <main>
        <section id="reservation">
            <div class="reservation-container">
                <div class="reservation-image">
                    <img src="../Images/reservation.jpg" alt="Ambiance relaxante de Serenity Spa">
                </div>
                <div class="reservation-form">
                    <h1>Réservez votre moment de Serenity</h1>
                    <form id="reservation-form" action="../server/rdv.php" method="POST">
                        <!-- Hidden Service ID -->
                        <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($service_id); ?>">

                        <!-- Date and Time -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="date">Date</label>
                                <input type="date" id="date" name="date" required>
                            </div>
                            <div class="form-group">
                                <label for="time">Heure</label>
                                <input type="time" id="time" name="time" required>
                            </div>
                        </div>

                        <!-- Duree (Duration) -->
                        <div class="form-group">
                            <label for="duree">Durée</label>
                            <select id="duree" name="duree" required>
                                <option value="" disabled selected>Choisissez la durée</option>
                                <option value="1">1 heure</option>
                                <option value="2">2 heures</option>
                                <option value="3">3 heures</option>
                                <option value="4">4 heures</option>
                            </select>
                        </div>

                        <!-- Commentaires -->
                        <div class="form-group">
                            <label for="commentaires">Commentaires</label>
                            <textarea id="commentaires" name="commentaires" rows="4" placeholder="Ajouter des commentaires ou demandes spéciales"></textarea>
                        </div>

                        <button type="submit">Confirmer la réservation</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
