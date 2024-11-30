<?php
// Start the session to store user session information
session_start();

// Ensure the form is being submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the form data
    $service = $_POST['service'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $personnel = $_POST['personnel'];
    $commentaires = $_POST['commentaires']; // New field for comments
    $id_banc = $_POST['id_banc']; // New field for Banc ID

    // Supabase API URL and Key (replace these with your actual Supabase URL and Key)
    $supabase_url = "https://jntnkbzdcznzdvbaurwe.supabase.co";
    $supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImpudG5rYnpkY3puemR2YmF1cndlIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTcyODMwNjUyOCwiZXhwIjoyMDQzODgyNTI4fQ.KO2-MUKsnC5T6sVB4HbkzHgxJN4U6ill5T7yRqvrcRQ"; // Service Role or anon key

    // Step 1: Check if the client is logged in (client_id should be in session)
    if (!isset($_SESSION['client_id'])) {
        // If not logged in, redirect to the login page
        echo "<script>alert('Vous devez être connecté pour faire une réservation.'); window.location.href='../Pages/login.html';</script>";
        exit();
    }

    // Retrieve client_id from the session
    $client_id = $_SESSION['client_id'];

    // Step 2: Map the selected service to the service ID
    $service_map = [
        'facial' => 1,
        'back' => 2,
        'foot' => 3,
        'thai' => 4
    ];

    $service_id = isset($service_map[$service]) ? $service_map[$service] : null;

    if (!$service_id) {
        // Invalid service, show an error
        echo "<script>alert('Service invalide sélectionné.'); window.location.href='../Pages/reservation.php';</script>";
        exit();
    }

    // Step 3: Combine date and time for comparison
    $reservation_datetime = $date . ' ' . $time;

    // Convert reservation datetime to Unix timestamp for comparison
    $reservation_timestamp = strtotime($reservation_datetime);
    $current_timestamp = time(); // Current time as Unix timestamp

    // Step 4: Validate if the reservation time is in the past
    if ($reservation_timestamp < $current_timestamp) {
        // If the reservation is in the past, show an error
        echo "<script>alert('La date et l\'heure de la réservation ne peuvent pas être dans le passé.'); window.location.href='../Pages/reservation.php';</script>";
        exit();
    }

    // Step 5: Prepare the data for insertion into the reservation table
    $reservation_data = [
        'date' => $date,
        'heure' => $time,
        'commentaires' => $commentaires, // Use the optional comments
        'date_creation' => date('Y-m-d H:i:s'),
        'id_client' => $client_id,
        'id_service' => $service_id,
        'id_banc' => $id_banc // Use the selected Banc ID
    ];

    // Step 6: Insert the reservation into the database via Supabase API using cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $supabase_url . '/rest/v1/reservation');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $supabase_key,
        'Content-Type: application/json',
        'apikey: ' . $supabase_key
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($reservation_data));

    // Execute the request to insert reservation
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get the response status code

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo "Erreur lors de la réservation: " . curl_error($ch);
        exit();
    }

    // Close cURL connection
    curl_close($ch);

    // Step 7: Check if the reservation was successfully inserted
    if ($http_code == 201) {
        // Success, show confirmation
        echo "<script>alert('Votre réservation a été confirmée.'); window.location.href='../Pages/dashboard.php';</script>";
    } else {
        // Error occurred
        echo "<script>alert('Erreur lors de la réservation. Veuillez réessayer plus tard.'); window.location.href='../Pages/reservation.php';</script>";
    }
}
?>
