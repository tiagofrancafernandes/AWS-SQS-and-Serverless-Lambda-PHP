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
        Schema::table('export_requests', function (Blueprint $table) {
            $table->string('user_id_type')->nullable();
            $table->string('user_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('export_requests', function (Blueprint $table) {
            $table->dropColumn('user_id_type');
            $table->dropColumn('user_id');
        });
    }
};
