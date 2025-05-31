<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStatutEnumInJustificatifsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // ATTENTION : Cette syntaxe dépend du SGBD. ENUM est propre à MySQL
        DB::statement("ALTER TABLE justificatifs MODIFY statut ENUM('Nouveau', 'Accepté', 'En cours', 'Refusé', 'Renvoyé') DEFAULT 'Nouveau'");
    }

    public function down()
    {
        // Remettre l'ancienne version si rollback
        DB::statement("ALTER TABLE justificatifs MODIFY statut ENUM('Nouveau', 'Accepté', 'En cours', 'Renvoyé') DEFAULT 'Nouveau'");
    }
}
