<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facebook_data_deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->string('confirmation_code', 32)->unique();
            $table->string('facebook_user_id', 100);
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->boolean('user_found')->default(false);
            $table->json('signed_request_payload')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('completed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facebook_data_deletion_requests');
    }
};
