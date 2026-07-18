<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 20)->default('admin')->after('email');
            }
            if (!Schema::hasColumn('users', 'failed_login_attempts')) {
                $table->unsignedTinyInteger('failed_login_attempts')->default(0)->after('password');
            }
            if (!Schema::hasColumn('users', 'locked_until')) {
                $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            }
        });

        // Existing accounts keep full admin access.
        DB::table('users')->where(function ($q) {
            $q->whereNull('role')->orWhere('role', '');
        })->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'locked_until')) {
                $table->dropColumn('locked_until');
            }
            if (Schema::hasColumn('users', 'failed_login_attempts')) {
                $table->dropColumn('failed_login_attempts');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
