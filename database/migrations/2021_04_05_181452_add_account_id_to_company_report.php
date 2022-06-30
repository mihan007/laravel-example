<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountIdToCompanyReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->change();
        });

          Schema::table('company_report', function (Blueprint $table) {
             $table->foreignId('account_id')->nullable()->constrained('accounts');
          });

        DB::statement(' UPDATE company_report cr
            INNER JOIN companies c ON cr.company_id = c.id
            SET cr.account_id = c.account_id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_report', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
        });
    }
}
