<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Etudiant;
use App\Services\FirebaseNotificationService;

class EnvoyerNotificationEtudiant implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $etudiantId;
    protected $sessionData;

    public function __construct(int $etudiantId, array $sessionData)
    {
        \Log::info("Création de job pour étudiant ID: {$etudiantId}");
        $this->etudiantId = $etudiantId;
        $this->sessionData = $sessionData;
    }

    public function handle(FirebaseNotificationService $firebaseService)
{
    \Log::info("Début du traitement pour étudiant ID: {$this->etudiantId}");

    $etudiant = Etudiant::find($this->etudiantId);
    
    if (!$etudiant) {
        \Log::error("Étudiant non trouvé", ['id' => $this->etudiantId]);
        return;
    }

    \Log::info("Étudiant trouvé", [
        'id' => $etudiant->id,
        'nom' => $etudiant->nom,
        'device_token' => $etudiant->device_token
    ]);

    if (empty($etudiant->device_token)) {
        \Log::warning("Device token manquant", ['etudiant_id' => $etudiant->id]);
        return;
    }

    try {
        \Log::info("Tentative d'envoi de notification", [
            'token' => $etudiant->device_token,
            'data' => $this->sessionData
        ]);
        
        $result = $firebaseService->sendNotification(
            $etudiant->device_token,
            'Session lancée',
            "Votre session '{$this->sessionData['course']}' vient de commencer.",
            '/student-course',
            $this->sessionData,
            'modal'
        );

        \Log::info("Résultat de l'envoi", ['result' => $result]);

    } catch (\Exception $e) {
        \Log::error("Échec de l'envoi", [
            'error' => $e->getMessage(),
            'stack' => $e->getTraceAsString()
        ]);
        $this->fail($e);
    }
}
}

