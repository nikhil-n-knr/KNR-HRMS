<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('crm_email_account_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_account_id')->constrained('crm_email_accounts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['email_account_id', 'user_id']);
        });

        // Migrate existing user_id
        $accounts = DB::table('crm_email_accounts')->whereNotNull('user_id')->get();
        foreach ($accounts as $account) {
            DB::table('crm_email_account_user')->insert([
                'email_account_id' => $account->id,
                'user_id' => $account->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('crm_email_accounts', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_email_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable();
        });

        Schema::dropIfExists('crm_email_account_user');
    }
};
