<?php

namespace App\Notifications\Templates;

class NotificationTemplate
{
    public static function courseCancelled($matiere, $heure): array
    {
        return [
            'title' => 'Cours annulé',
            'message' => "Le cours de $matiere prévu à $heure a été annulé.",
            'type' => 'info',
        ];
    }

    public static function lowAttendanceAlert($matiere): array
    {
        return [
            'title' => 'Alerte de présence',
            'message' => "Vous avez manqué plusieurs cours de $matiere. Veuillez régulariser votre situation.",
            'type' => 'alert',
        ];
    }

    public static function weeklySurvey($salle): array
    {
        return [
            'title' => 'Sondage hebdomadaire',
            'message' => "Merci de répondre à ce sondage concernant la salle $salle.",
            'type' => 'survey',
        ];
    }

    public static function courseUpdated($matiere, $heure)
    {
        return [
            'title' => 'Cours modifié',
            'message' => "Le cours de $matiere a été modifié...",
            'type' => 'warning', // ou 'alert', selon l’usage
        ];
    }


    // Tu peux ajouter autant de templates que tu veux ici
}
