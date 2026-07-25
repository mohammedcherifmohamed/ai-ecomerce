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
        Schema::create('ready_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId("inquiry_id")->constrained("inquiry","id");
            $table->foreignId("customer_id")->nullable()->constrained("customers","id");
            $table->string("title");
            $table->text("email");
            $table->boolean("email_sent")->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ready_emails');
    }
};
