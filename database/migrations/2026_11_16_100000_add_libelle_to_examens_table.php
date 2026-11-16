<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Le libelle d'une session d'examen : ce que les documents impriment.
 *
 * Le titre reste le code de travail (« BTS-S1 ») ; le libelle dit la meme
 * chose en toutes lettres (« BTS — Semestre 1 »), et c'est lui qui part sur
 * les bulletins et les releves. Les sessions deja enregistrees recoivent un
 * libelle deduit de leur titre, modifiable ensuite a la main.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('examens', 'libelle')) {
            Schema::table('examens', function (Blueprint $table) {
                $table->string('libelle')->nullable()->after('titre');
            });
        }

        DB::table('examens')->whereNull('libelle')->orWhere('libelle', '')
            ->get(['id', 'titre'])
            ->each(function ($examen) {
                DB::table('examens')->where('id', $examen->id)->update([
                    'libelle' => \App\Models\Examen::libelleDepuisTitre($examen->titre),
                ]);
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('examens', 'libelle')) {
            Schema::table('examens', function (Blueprint $table) {
                $table->dropColumn('libelle');
            });
        }
    }
};
