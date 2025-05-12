<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->string('statut')->default('en_attente'); // ou "en_cours", "terminée"
            $table->dateTime('heure_debut');
            $table->dateTime('heure_fin');
            $table->string('lien')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('salle_id')->nullable()->constrained('salles')->onDelete('set null');
            $table->foreignId('matiere_id')->nullable()->constrained('matieres')->onDelete('set null');
            $table->foreignId('filere_id')->nullable()->constrained('fileres')->onDelete('set null');
            $table->foreignId('niveau_id')->nullable()->constrained('niveaux')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sessions');
    }
}
