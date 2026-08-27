<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Path relatif di disk "public" (mis. attendance-photos/xxx.jpg).
            // Nullable karena absensi lama tidak punya foto dan foto tetap opsional.
            $table->string('check_in_photo')->nullable()->after('check_in_long');
            $table->string('check_out_photo')->nullable()->after('check_out_long');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['check_in_photo', 'check_out_photo']);
        });
    }
};
