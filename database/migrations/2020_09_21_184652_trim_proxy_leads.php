<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TrimProxyLeads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "UPDATE proxy_leads SET phone=trim(both char(10) from phone), name=trim(both char(10) from name), comment=trim(both char(10) from comment);";
        DB::statement($sql);
        $sql = "UPDATE proxy_leads SET phone=trim(both char(13) from phone), name=trim(both char(13) from name), comment=trim(both char(13) from comment);";
        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
