<?php
// signup_process.php

// Start the session to store user session information
session_start();

// Ensure the form is being submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the form data
    $name = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];
    $num_tel = $_POST['num_tel'];
    $adresse = $_POST['adresse'];
    $nationalite = $_POST['nationalite']; // Get the nationality

    // Validate that the passwords match
    if ($password !== $confirm_password) {
        echo "<script>alert('Les mots de passe ne correspondent pas.'); window.location.href = '../Pages/signup.html';</script>";
        exit();
    }

    // Optional: Validate the phone number (for example, check if it's a valid format)
    if (!preg_match("/^\+?[0-9]{10,15}$/", $num_tel)) {
        echo "<script>alert('Veuillez entrer un numéro de téléphone valide.'); window.location.href = '../Pages/signup.html';</script>";
        exit();
    }

    // Hash the password before storing it
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Supabase API URL and Key (replace these with your actual Supabase URL and Key)
    $supabase_url = "https://ulxorjunckbvzwchthyc.supabase.co";
    $supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InVseG9yanVuY2tidnp3Y2h0aHljIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTczMzA3ODgyNywiZXhwIjoyMDQ4NjU0ODI3fQ.r2WEhkouIq8ktFRRHTVrColYAIEOYc-aonhk-f2Vm6A"; // Service Role or anon key

    // Prepare the data to insert into Supabase for the MEMBRE table
    $membre_data = [
        'nom' => $name,
        'prenom' => $prenom,
        'email' => $email,
        'num_tel' => $num_tel,
        'adresse' => $adresse,
        'nationalite' => $nationalite,  // Include the nationalité field here
    ];

    // Prepare the cURL request to insert the data into Supabase for the MEMBRE table
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $supabase_url . '/rest/v1/membre');  // Adjust the table name to match your schema
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $supabase_key,  // Correct header format
        'Content-Type: application/json',
        'Prefer: return=representation',
        'apikey: ' . $supabase_key // Add apiKey directly if Authorization header fails
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($membre_data));

    // Execute the request to insert into MEMBRE table
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get the response status code

    // Check for errors in the request
    if(curl_errno($ch)) {
        echo "<script>alert('Erreur lors de l\'inscription: " . curl_error($ch) . "'); window.location.href = '../Pages/signup.html';</script>";
        exit();
    }

    // Close cURL connection for MEMBRE insertion
    curl_close($ch);

    // Debug: Check response status and body for better error tracking
    if ($http_code != 201) {
        echo "<script>alert('Erreur lors de l\'inscription du membre. Statut: $http_code, Réponse: $response'); window.location.href = '../Pages/signup.html';</script>";
        exit();
    }

    // Decode the response to retrieve the inserted MEMBRE ID
    $membre_response = json_decode($response, true);

    if ($membre_response && isset($membre_response[0]['id_membre'])) {
        $id_membre = $membre_response[0]['id_membre']; // Get the last inserted member ID

        // Prepare the data to insert into the CLIENT table
        $client_data = [
            'password' => $hashed_password,  // The hashed password
            'id_membre' => $id_membre,      // The ID of the inserted member (from the previous step)
        ];

        // Log client data for debugging
        file_put_contents('client_log.txt', "Client Data: " . print_r($client_data, true), FILE_APPEND);

        // Prepare the cURL request to insert into Supabase for the CLIENT table
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $supabase_url . '/rest/v1/client');  // Adjust the table name to match your schema
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $supabase_key,
            'Content-Type: application/json',
            'Prefer: return=representation',
            'apikey: ' . $supabase_key // Add apiKey directly if Authorization header fails
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($client_data));

        // Execute the request to insert into CLIENT table
        $client_response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get the response status code

        // Check for errors in the request
        if(curl_errno($ch)) {
            echo "<pre>Error: " . curl_error($ch) . "</pre>";
            exit();
        }

        // Display response for debugging
        echo "<pre>Client Response: " . $client_response . "</pre>";
        echo "<pre>HTTP Code: " . $http_code . "</pre>";

        // Close cURL connection for CLIENT insertion
        curl_close($ch);

        // Debug: Check response status and body for better error tracking
        if ($http_code != 201) {
            echo "<script>alert('Erreur lors de l\'inscription du client. Statut: $http_code, Réponse: $client_response'); window.location.href = '../Pages/signup.html';</script>";
            exit();
        }

        // Decode the client response
        $client_response_data = json_decode($client_response, true);

        if ($client_response_data && isset($client_response_data[0]['id_client'])) {
            // Store the client ID in the session to log the user in
            $_SESSION['client_id'] = $client_response_data[0]['id_client'];
            echo "<script>window.location.href = '../Pages/dashboard.php';</script>";
            exit();
        } else {
            echo "<script>alert('Erreur lors de la création du client.'); window.location.href = '../Pages/signup.html';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Erreur lors de l\'inscription du membre.'); window.location.href = '../Pages/signup.html';</script>";
        exit();
    }
}
?>
