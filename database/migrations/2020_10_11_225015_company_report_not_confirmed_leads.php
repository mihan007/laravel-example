<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CompanyReportNotConfirmedLeads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_report', function ($table) {
            $table->integer('not_confirmed_leads')->default(0)->after('target_percent');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_report', function ($table) {
            $table->dropColumn('not_confirmed_leads');
        });
    }
}
