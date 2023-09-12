<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


// ONLY NEEDED FOR APPLICATIONS UPDATING TO VERSION 1.0!
return new class extends Migration {
    public function up(): void
    {
        /**
         * Table to store all ODK raw submissions that get pulled from ODK Central
         */
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex('primary');
            $table->renameColumn('id', 'odk_id');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->id();
        });

    }

};
