<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSallesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salles', function (Blueprint $table) {
            $table->id();
            $table->decimal("latitude");
            $table->decimal("longitude");
            $table->decimal("rayon_metres");
            $table->timestamps();
        });
        
// Ajouter la contrainte CHECK après la création
DB::statement('ALTER TABLE salles ADD CONSTRAINT check_rayon_positive CHECK (rayon_metres > 0)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salles');
    }
}
