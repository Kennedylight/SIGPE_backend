<?php

require __DIR__ . '/../vendor/autoload.php'; // Adapter le chemin si besoin

use Google\Auth\Credentials\ServiceAccountCredentials;

$pathToJsonKey = __DIR__ . '/firebase_credentials.json'; // Le fichier JSON copié ici

$credentials = new ServiceAccountCredentials(
    'https://www.googleapis.com/auth/firebase.messaging',
    json_decode(file_get_contents($pathToJsonKey), true)
);

$token = $credentials->fetchAuthToken();
echo "Access Token: " . $token['access_token'] . "\n";
