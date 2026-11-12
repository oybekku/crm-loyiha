<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // "Baza" — eskirgan loyihalar. baza_at to'lgan loyiha asosiy saytning hamma
        // ro'yxati/statistikasidan yashiriladi (Project global scope), faqat admin
        // "Baza" bo'limida ko'radi. Hech narsa o'chirilmaydi — qaytarish mumkin.
        // baza_snapshot — ko'chirishdan oldingi holat (status, xizmatlar), qaytarish uchun.
        Schema::table('projects', function (Blueprint $table) {
            $table->timestamp('baza_at')->nullable()->index()->after('status');
            $table->unsignedBigInteger('baza_by')->nullable()->after('baza_at');
            $table->json('baza_snapshot')->nullable()->after('baza_by');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['baza_at']);
            $table->dropColumn(['baza_at', 'baza_by', 'baza_snapshot']);
        });
    }
};
