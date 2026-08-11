<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('applicant_type')->nullable()->after('pinfl');
            $table->string('cadastre_number')->nullable()->after('applicant_type');
            $table->string('region')->nullable()->after('cadastre_number');
            $table->string('district')->nullable()->after('region');
            $table->string('registration_basis')->nullable()->after('district');
            $table->string('ownership_document_path')->nullable()->after('registration_basis');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'applicant_type',
                'cadastre_number',
                'region',
                'district',
                'registration_basis',
                'ownership_document_path',
            ]);
        });
    }
};
