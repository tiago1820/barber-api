<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('userappointments', function(Blueprint $table) {
            $table->integer('id_barber')
                ->after('id_user');
        });
        Schema::create('all_tables', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('all_tables');
        Schema::table('userappointments', function(Blueprint $table) {
            $table->dropColumn('id_barber');
        });

    }
};
