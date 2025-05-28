<?php

namespace App\Services;

use Google\Client;
use Illuminate\Support\Facades\Http;

class FirebaseNotificationService
{
    protected $accessToken;
    protected $projectId;

    public function __construct()
    {
        // Chemin vers le fichier de credentials
        $path = storage_path('app/firebase/firebase_credentials.json');

        $this->projectId = config('services.firebase.project_id');

        // Initialisation du client Google
        $client = new Client();
        $client->setAuthConfig($path);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $this->accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];
    }

    // public function sendNotification($deviceToken, $title, $body)
    // {
    //     $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

    //     $response = Http::withToken($this->accessToken)->post($url, [
    //         'message' => [
    //             'token' => $deviceToken,
    //             'notification' => [
    //                 'title' => $title,
    //                 'body' => $body
    //             ],
    //         ]
    //     ]);

    //     return $response->json();
    // }

//     public function sendNotification($deviceToken, $title, $body, $redirectUrl = null)
// {
//     $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

//     $message = [
//         'message' => [
//             'token' => $deviceToken,
//             'notification' => [
//                 'title' => $title,
//                 'body' => $body,
//             ],
//             'data' => [
//                 'redirect' => $redirectUrl ?? '/',
//             ],
//             'webpush' => [
//                 'fcm_options' => [
//                     'link' => 'http://localhost:8100/student-course', // à adapter
//                 ]
//             ]
//         ]
//     ];

//     $response = Http::withToken($this->accessToken)
//         ->withHeaders(['Content-Type' => 'application/json'])
//         ->post($url, $message);

//     return $response->json();
// }

    // public function sendNotification($deviceToken, $title, $body, $redirectUrl = '/student-course', $data = [])
    // {
    //     $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

    //     // ➕ Fusionne les données de redirection avec celles personnalisées
    //     // $data = array_merge(['redirect' => $redirectUrl], $data);

    //     $message = [
    //         'message' => [
    //             'token' => $deviceToken,
    //             'notification' => [
    //                 'title' => $title,
    //                 'body' => $body,
    //             ],
    //             'data' => $data,
    //             'webpush' => [
    //                 'fcm_options' => [
    //                     'link' => "http://localhost:8100$redirectUrl"
    //                 ]
    //             ]
    //         ]
    //     ];

    //     $response = Http::withToken($this->accessToken)
    //         ->withHeaders(['Content-Type' => 'application/json'])
    //         ->post($url, $message);

    //     return $response->json();
    // }


public function sendNotification($deviceToken, $title, $body, $redirectUrl = '/student-course')
{
    $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

    $message = [
        'message' => [
            'token' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => [
                'redirect' => $redirectUrl, // Utilisé dans le foreground
            ],
            'webpush' => [
                'fcm_options' => [
                    'link' => "http://localhost:8100$redirectUrl" // Utilisé en background
                ]
            ]
        ]
    ];

    $response = Http::withToken($this->accessToken)
        ->withHeaders(['Content-Type' => 'application/json'])
        ->post($url, $message);

    return $response->json();
}


}
