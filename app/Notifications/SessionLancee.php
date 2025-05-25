<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;


class SessionLancee extends Notification
{
    use Queueable;
    public $session;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($session)
    {
        $this->session = $session;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'fcm'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toDatabase($notifiable)
    {
        return [
                  "title"=>"Nouvelle session Lancee",
                  "session_id" => $this->session->id

        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    public function toFcm($notifiable)
    {
        // if (!$notifiable->device_token) {
        //     return;
        // }
        $token = $notifiable->routeNotificationFor('fcm', $this);

        if (!$token) return;


        $data = [
            "to" => $notifiable->device_token,
            "notification" => [
                "title" => "Nouvelle session lancée",
                "body" => "Une session a été lancée pour ta filière et ton niveau.",
                "sound" => "default"
            ],
            "data" => [
                "session_id" => $this->session->id,
                "redirect" => "/student-course-page"
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => 'key=' . config('services.fcm.server_key'),
            'Content-Type' => 'application/json'
        ])->post('https://fcm.googleapis.com/fcm/send', $data);

        \Log::info('FCM response', ['body' => $response->body()]);

        return $response;
        
        // return Http::withHeaders([
        //     'Authorization' => 'key=' . config('services.fcm.server_key'),
        //     'Content-Type' => 'application/json'
        // ])->post('https://fcm.googleapis.com/fcm/send', $data);
    }


    // public function toFcm($notifiable)
    // {
    //     if (!$notifiable->device_token) {
    //         return;
    //     }

    //     $data = [
    //         "to" => $notifiable->device_token,
    //         "notification" => [
    //             "title" => "Nouvelle session lancée",
    //             "body" => "Une session a été lancée pour ta filière et ton niveau.",
    //             "sound" => "default"
    //         ],
    //         "data" => [
    //             "session_id" => $this->session->id
    //         ]
    //     ];

    //     Http::withHeaders([
    //         'Authorization' => 'key=' . config('services.fcm.server_key'),
    //         'Content-Type' => 'application/json'
    //     ])->post('https://fcm.googleapis.com/fcm/send', $data);
    // }
}
