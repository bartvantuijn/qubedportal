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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('billing_street')->nullable()->after('phone');
            $table->string('billing_postcode')->nullable()->after('billing_street');
            $table->string('billing_city')->nullable()->after('billing_postcode');
            $table->enum('invoice_language', ['nl', 'en'])->default('nl')->after('billing_city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'billing_street',
                'billing_postcode',
                'billing_city',
                'invoice_language',
            ]);
        });
    }
};
