<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Affectation d'un membre du personnel : le departement, la filiere ou la
 * specialite dont il a la charge pour une annee academique.
 *
 * Une ligne = une charge. Un enseignant en cumule plusieurs : il intervient
 * dans deux specialites, et coordonne une filiere. C'est ce qui decide de ce
 * qu'il voit dans son espace, et des documents qu'on peut lui demander.
 *
 * Le niveau dit ou la charge a ete posee ; les colonnes parentes sont
 * remplies par deduction (une specialite appartient a une filiere, qui
 * appartient a un departement) pour que « tout le personnel du departement »
 * soit une seule requete.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affectations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('niveau', ['departement', 'filiere', 'specialite']);
            $table->unsignedBigInteger('departement_id')->nullable();
            $table->unsignedBigInteger('filiere_id')->nullable();
            $table->unsignedBigInteger('specialite_id')->nullable();
            $table->string('annee_academique', 20);
            // Ce que la personne y fait : « Enseignant », « Chef de
            // departement »… Libre, parce que les intitules varient.
            $table->string('fonction', 120)->nullable();
            $table->timestamps();

            $table->index(['annee_academique', 'departement_id']);
            $table->index(['annee_academique', 'filiere_id']);
            $table->index(['annee_academique', 'specialite_id']);
            $table->unique(
                ['user_id', 'niveau', 'departement_id', 'filiere_id', 'specialite_id', 'annee_academique'],
                'affectations_unicite'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affectations');
    }
};
