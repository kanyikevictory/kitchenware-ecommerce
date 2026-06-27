<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles')->nullOnDelete();
            $table->string('phone')->nullable()->unique()->after('email');
            $table->string('status')->default('active')->after('remember_token');
            $table->timestamp('last_login_at')->nullable()->after('status');

            $table->index(['role_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropUnique(['phone']);
            $table->dropColumn(['phone', 'status', 'last_login_at']);
        });
    }
};
