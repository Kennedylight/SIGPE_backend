<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
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
            $table->string('prenom')->nullable();
            $table->string('sexe');
            $table->string('Date_nais');
            $table->double("longitude");
            $table->double("latitude");
            $table->string('email')->nullable();
            $table->foreignId('filiere_id')->constrained('filieres')->onDelete('set null');
            $table->foreignId('niveau_id')->constrained('niveaux')->onDelete('set null'); // Ex : L1, L2, L3, M1...
            $table->string('photo')->nullable(); 
            $table->string('utilisateur')->default("ETU");
            $table->string('password')->default(Hash::make("00000000")); 
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
