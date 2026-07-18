<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 50)->nullable()->unique()->after('name');
            }
        });

        User::query()->whereNull('username')->orWhere('username', '')->orderBy('id')->each(function (User $user) {
            $base = Str::lower(Str::before((string) $user->email, '@'));
            $base = preg_replace('/[^a-z0-9_\.\-]+/', '', $base) ?: 'user';
            $candidate = $base;
            $i = 1;
            while (User::where('username', $candidate)->where('id', '!=', $user->id)->exists()) {
                $candidate = $base . $i;
                $i++;
            }
            $user->forceFill(['username' => $candidate])->save();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'username')) {
                $table->dropUnique(['username']);
                $table->dropColumn('username');
            }
        });
    }
};
