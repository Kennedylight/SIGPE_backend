<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPresenceIdToJustificatifsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('justificatifs', function (Blueprint $table) {
            $table->foreignId('presence_id')->constrained('presences')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('justificatifs', function (Blueprint $table) {
            $table->dropColumn('presence_id');
        });
    }
}
