<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('date_naissance')->nullable()->after('matricule');
            $table->string('lieu_naissance')->nullable()->after('date_naissance');
            $table->enum('sexe', ['M', 'F'])->nullable()->after('lieu_naissance');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['date_naissance', 'lieu_naissance', 'sexe']);
        });
    }
};
