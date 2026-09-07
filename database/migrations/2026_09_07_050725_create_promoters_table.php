<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promoters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(10.00); // percentage the company keeps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promoters');
    }
};
