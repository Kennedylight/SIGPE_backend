<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEtudiantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('etudiants', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->foreignId('filiere_id')->nullable()->constrained('fileres')->onDelete('set null');
            $table->string('niveau');
            $table->foreignId('niveau_id')->nullable()->constrained('niveaux')->onDelete('set null'); // Ex : L1, L2, L3, M1...
            $table->string('photo'); 
            $table->string('utilisateur')->default("ETU");
            $table->string('password'); 
            $table->string('matricule')->unique(); // identifiant scolaire
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
        Schema::dropIfExists('etudiants');
    }
}
