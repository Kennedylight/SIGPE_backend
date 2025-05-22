<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('presences', function (Blueprint $table) {
        $table->id();

        // Clés étrangères
        $table->foreignId('session_id')->constrained()->onDelete('cascade');
        $table->foreignId('etudiant_id')->constrained()->onDelete('cascade');

        // Statut de présence
        $table->enum('statut', ['absent', 'présent', 'en retard', 'excusé'])->default('absent');

        $table->timestamps();

        // Optionnel : empêcher les doublons
        $table->unique(['session_id', 'etudiant_id']);
    });
}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('presences');
    }
}
