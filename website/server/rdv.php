<?php
// Start the session to store user session information
session_start();

// Ensure the form is being submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the form data
    $date = $_POST['date'];
    $time = $_POST['time'];
    $commentaires = $_POST['commentaires']; // New field for comments
    $duree = $_POST['duree']; // Duration field (1-4 hours)
    $service_id = $_POST['service_id']; // Service ID passed via hidden input

    // Supabase API URL and Key (replace these with your actual Supabase URL and Key)
    $supabase_url = "https://ulxorjunckbvzwchthyc.supabase.co";
    $supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InVseG9yanVuY2tidnp3Y2h0aHljIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTczMzA3ODgyNywiZXhwIjoyMDQ4NjU0ODI3fQ.r2WEhkouIq8ktFRRHTVrColYAIEOYc-aonhk-f2Vm6A"; // Service Role or anon key
    
    // Step 1: Check if the client is logged in (client_id should be in session)
    if (!isset($_SESSION['client_id'])) {
        // If not logged in, redirect to the login page
        echo "<script>alert('Vous devez être connecté pour faire une réservation.'); window.location.href='../Pages/login.html';</script>";
        exit();
    }

    // Retrieve client_id from the session
    $client_id = $_SESSION['client_id'];

    // Step 2: Validate that the duration is between 1 and 4 hours
    if ($duree < 1 || $duree > 4) {
        echo "<script>alert('La durée de la réservation doit être entre 1 et 4 heures.'); window.location.href='../Pages/reservation.php';</script>";
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
        'statut' => 'Pending',  // Default status is 'Pending'
        'commentaires' => $commentaires, // Use the optional comments
        'date_creation' => date('Y-m-d H:i:s'),
        'duree' => $duree, // Duration passed from form
        'id_client' => $client_id,
        'id_service' => $service_id // Service ID passed from the form
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
        // Generate the QR code here
        include_once('../phpqrcode/qrlib.php'); // Make sure the library is included

        // Define data to encode in QR
        $qr_data = [
            'type' => 'fonctionnel', // or 'not' depending on your logic
            'id_membre' => $client_id
        ];

        // Convert data to query string or JSON
        $qr_data_string = json_encode($qr_data);

        // Path to save the QR code image
        $qr_code_path = '../qr_codes/qr_code_' . $client_id . '.png';

        // Generate the QR code
        QRcode::png($qr_data_string, $qr_code_path);

        // Redirect to the page that displays the QR code
        header("Location: ../Pages/show_qr_code.php?file=" . urlencode($qr_code_path));
        exit();
    } else {
        // Error occurred
        echo "<script>alert('Erreur lors de la réservation. Veuillez réessayer plus tard.'); window.location.href='../Pages/reservation.php';</script>";
    }
}
?>
