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
        Schema::create('export_requests', function (Blueprint $table) {
            $table->id();
            $table->string('resource_name')->index();
            $table->json('mapped_columns');
            $table->string('tenant_id')->nullable()->index();
            $table->json('modifiers')->nullable();
            $table->datetime('request_date')->nullable()->index();
            $table->string('report_file_path')->nullable();
            $table->string('report_file_disk')->nullable();
            $table->string('final_file_path')->nullable();
            $table->string('final_file_disk')->nullable();
            $table->boolean('was_finished_successfully')->nullable();
            $table->integer('status')->index()->nullable();
            $table->integer('final_status')->index()->nullable();
            $table->longText('log')->nullable();
            $table->string('disk_name')->nullable();
            $table->boolean('was_finished')->default(false);
            $table->string('sqs_message_id')->nullable();
            $table->json('sqs_request_info')->nullable(); // Records.0.attributes[]
            $table->longText('sqs_message_body')->nullable();
            $table->json('sqs_message_attributes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_requests');
    }
};
