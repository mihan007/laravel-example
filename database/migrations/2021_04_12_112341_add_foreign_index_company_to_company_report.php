<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Domain\Company;

class AddForeignIndexCompanyToCompanyReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $emptyCompaniesIds = DB::table('company_report')
            ->leftJoin('companies', 'company_report.company_id', '=', 'companies.id')
            ->whereNull('companies.id')
            ->distinct()
            ->pluck('company_report.company_id');

        DB::table('company_report')->whereIn('company_report.company_id', $emptyCompaniesIds)->delete();

        Schema::table('company_report', function (Blueprint $table) {
            $table->unsignedInteger('company_id')->change();

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_report', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });
    }
}
