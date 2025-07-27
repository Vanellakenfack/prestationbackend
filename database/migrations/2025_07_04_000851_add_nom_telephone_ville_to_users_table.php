<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('nom')->after('id');
        $table->string('telephone')->after('type');
        $table->string('ville')->after('telephone');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['nom', 'telephone', 'ville']);
    });
}
};
