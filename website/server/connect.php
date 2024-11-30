<?php
// Start the session to store user session information
session_start();

// Ensure the form is being submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Supabase API URL and Key (replace these with your actual Supabase URL and Key)
    $supabase_url = "https://jntnkbzdcznzdvbaurwe.supabase.co";
    $supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImpudG5rYnpkY3puemR2YmF1cndlIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTcyODMwNjUyOCwiZXhwIjoyMDQzODgyNTI4fQ.KO2-MUKsnC5T6sVB4HbkzHgxJN4U6ill5T7yRqvrcRQ"; // Service Role or anon key

    // Step 1: Get the membre_id using email from the "membre" table
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $supabase_url . '/rest/v1/membre?email=eq.' . urlencode($email));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $supabase_key,
        'Content-Type: application/json',
        'apikey: ' . $supabase_key
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

    // Execute the request to fetch membre details
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get the response status code

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo "Erreur lors de la connexion: " . curl_error($ch);
        exit();
    }

    // Close cURL connection
    curl_close($ch);

    // Decode the response
    $membre_data = json_decode($response, true);

    // Step 2: Check if the email exists in the "membre" table
    if ($http_code == 200 && isset($membre_data[0])) {
        // Get the membre_id from the response
        $membre_id = $membre_data[0]['id_membre'];

        // Step 3: Use the membre_id to get the password from the "client" table
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $supabase_url . '/rest/v1/client?id_membre=eq.' . $membre_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $supabase_key,
            'Content-Type: application/json',
            'apikey: ' . $supabase_key
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        // Execute the request to fetch client details
        $client_response = curl_exec($ch);
        $client_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo "Erreur lors de la connexion au client: " . curl_error($ch);
            exit();
        }

        // Close cURL connection for the client
        curl_close($ch);

        // Decode the client response
        $client_data = json_decode($client_response, true);

        // Step 4: Check if the client exists and verify the password
        if ($client_http_code == 200 && isset($client_data[0])) {
            // Get the stored password from the client table
            $stored_password = $client_data[0]['password'];  // The hashed password stored in the database

            // Verify the provided password
            if (password_verify($password, $stored_password)) {
                // Login successful, store client_id in the session
                $_SESSION['client_id'] = $client_data[0]['id_client'];

                // Redirect to the dashboard or another page after successful login
                header('Location: ../Pages/dashboard.php');
                exit();
            } else {
                // Password incorrect
                echo "<script>alert('Mot de passe incorrect.'); window.location.href='../Pages/login.html';</script>";
            }
        } else {
            // Client not found
            echo "<script>alert('L\'utilisateur n\'existe pas.'); window.location.href='../Pages/login.html';</script>";
        }
    } else {
        // Membre not found
        echo "<script>alert('Email incorrect.'); window.location.href='../Pages/login.html';</script>";
    }
}
?>
