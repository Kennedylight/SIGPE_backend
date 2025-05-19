<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnseignantIdToSessionsTable extends Migration
{
    public function up(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('enseignant_id')->nullable()->after('salle_id');
            $table->foreign('enseignant_id')->references('id')->on('enseignants')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['enseignant_id']);
            $table->dropColumn('enseignant_id');
        });
    }
}
