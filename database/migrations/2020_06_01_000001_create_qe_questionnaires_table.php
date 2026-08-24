<?php

/*
 * 🚨 TIDSSTEMPLET ER BEVIDST 2020_06_01 — laeg det ALDRIG tilbage til
 * 0001_01_01.
 *
 * Praefikset 0001_01_01 er Laravels konvention for FRAMEWORKETS egne
 * migrationer (users, cache, jobs), som skal koere allerfoerst. Men denne
 * pakkes tabeller har fremmednoegler UDAD til vaertsapplikationens tabeller,
 * og de kan pr. definition ikke eksistere paa det tidspunkt.
 *
 * Maalt 24-08-2026 mod en frisk MySQL 8.4:
 *   SQLSTATE[HY000] 1824: Failed to open the referenced table 'teams'
 * Migrationen fejlede paa den FOERSTE af pakkens filer, og vaertsprojektets
 * migrate stoppede efter 2 tabeller (migrations + qe_questionnaires).
 * Med 2020_06_01: 164 tabeller.
 *
 * 🔑 Hvorfor ingen opdagede det foer: sqlite haandhaever ikke fremmednoegler
 * ved ALTER TABLE som standard, og vaertsprojektets CI koerer sqlite. Fejlen
 * var usynlig fra pakken blev tilfoejet til foerste MySQL-opsaetning fra bunden.
 *
 * 2020_06_01 ligger efter Jetstream/Teams (2020_05_21).
 *
 * 🪤 Det er IKKE nok for alle vaertstabeller. `subject` er konfigurerbar, og i
 * Frankston-master er det `clients`, oprettet 2023_03_07 — altsaa EFTER dette
 * tidsstempel. Create-migrationen falder da tilbage til en bar
 * unsignedBigInteger uden constraint. Derfor findes
 * 2026_08_24_120000_add_host_foreign_keys_to_qe_tables, som koerer SIDST og
 * saetter de udadgaaende noegler uanset vaertens raekkefoelge. Et tidsstempel
 * alene kan ikke loese det; det kan kun flytte problemet.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable($this->prefix().'questionnaires')) {
            return;
        }

        // 🪤 Samme degradering som 000006: vaertens company-tabel findes ikke
        // noedvendigvis endnu. Uden dette tjek fejler migrate HAARDT paa
        // pakkens FOERSTE fil med 1824 — praecis den fejlklasse omdoebningen
        // findes for at fjerne. Noeglen saettes i saa fald af
        // 2026_08_24_120000_add_host_foreign_keys_to_qe_tables.
        $companyTable = $this->getCompanyTable();
        $companyTableExists = Schema::hasTable($companyTable);

        Schema::create($this->prefix().'questionnaires', function (Blueprint $table) use ($companyTable, $companyTableExists) {
            $table->id();

            if ($companyTableExists) {
                $table->foreignId('company_id')->nullable()->constrained($companyTable)->cascadeOnDelete();
            } else {
                $table->unsignedBigInteger('company_id')->nullable();
            }
            $table->string('type');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_template')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['company_id', 'type']);
        });
    }

    public function down(): void
    {
        // 🪤 Uden en down() kaster `migrate:rollback` fatal error — Laravels
        // Migration er abstrakt og har ingen default. Fundet ved review 24-08-2026.
        Schema::dropIfExists($this->prefix().'questionnaires');
    }


    protected function getCompanyTable(): string
    {
        return (new (config('questionnaire.models.company')))->getTable();
    }

    protected function prefix(): string
    {
        return config('questionnaire.table_prefix', 'qe_');
    }
};
