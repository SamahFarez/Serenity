<?php
// Supabase credentials
$supabase_url = "https://jntnkbzdcznzdvbaurwe.supabase.co";
$supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImpudG5rYnpkY3puemR2YmF1cndlIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTcyODMwNjUyOCwiZXhwIjoyMDQzODgyNTI4fQ.KO2-MUKsnC5T6sVB4HbkzHgxJN4U6ill5T7yRqvrcRQ"; // Service Role or anon key

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

// Fetch services and bancs data
$services = fetch_data_from_supabase('/rest/v1/service');
$bancs = fetch_data_from_supabase('/rest/v1/banc');

// Initialize personnel as an empty array
$personnel = [];

// If a banc is selected, fetch personnel for that banc
if (isset($_POST['id_banc'])) {
    $banc_id = $_POST['id_banc'];
    $personnel = fetch_data_from_supabase('/rest/v1/personnel?select=id_personnel,nom&id_banc=eq.' . $banc_id);
}
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
                        <!-- Service selection -->
                        <div class="form-group">
                            <label for="service">Service</label>
                            <select id="service" name="service" required>
                                <option value="" disabled selected>Choisissez un service</option>
                                <?php
                                // Loop through services and add them to the dropdown
                                foreach ($services as $service) {
                                    echo "<option value='" . $service['id_service'] . "'>" . htmlspecialchars($service['nom']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

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

                        <!-- Banc selection -->
                        <div class="form-group">
                            <label for="id_banc">Banc</label>
                            <select id="id_banc" name="id_banc" required>
                                <option value="" disabled selected>Choisissez un banc</option>
                                <?php
                                // Loop through bancs and add them to the dropdown
                                foreach ($bancs as $banc) {
                                    echo "<option value='" . $banc['id_banc'] . "'>" . htmlspecialchars($banc['nom']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Personnel selection (to be populated based on banc selection) -->
                        <div class="form-group">
                            <label for="personnel">Personnel</label>
                            <select id="personnel" name="personnel" required>
                                <option value="" disabled selected>Choisissez un personnel</option>
                                <?php
                                // Loop through personnel (if any) and add them to the dropdown
                                if (!empty($personnel)) {
                                    foreach ($personnel as $person) {
                                        echo "<option value='" . $person['id_personnel'] . "'>" . htmlspecialchars($person['nom']) . "</option>";
                                    }
                                } else {
                                    echo "<option value='' disabled>Aucun personnel disponible</option>";
                                }
                                ?>
                            </select>
                        </div>

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
