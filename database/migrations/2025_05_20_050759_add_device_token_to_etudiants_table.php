<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeviceTokenToEtudiantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('etudiants', function (Blueprint $table) {
        $table->string('device_token')->nullable()->after('email'); // ou autre colonne proche
    });
}

public function down()
{
    Schema::table('etudiants', function (Blueprint $table) {
        $table->dropColumn('device_token');
    });
}
}
