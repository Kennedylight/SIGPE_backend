<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReponseEnseignantToJustificatifsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('justificatifs', function (Blueprint $table) {
            $table->text('reponse_enseignant')->nullable()->after('piece_jointes');
        });
    }

    public function down()
    {
        Schema::table('justificatifs', function (Blueprint $table) {
            $table->dropColumn('reponse_enseignant');
        });
    }
}
