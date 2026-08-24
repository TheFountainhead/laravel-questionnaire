<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tilfoej de fremmednoegler der peger UDAD paa vaertsapplikationens tabeller.
 *
 * 🚨 Hvorfor de ikke kan staa i create-migrationerne.
 *
 * Pakken kender ikke vaertens migrations-raekkefoelge. Create-migrationerne
 * ligger paa 2020_06_01 for at komme efter Jetstream/Teams (2020_05_21) — men
 * `subject`-tabellen er konfigurerbar, og i Frankston-master er det `clients`,
 * der oprettes **2023_03_07**. Paa det tidspunkt findes den altsaa ikke.
 *
 * Create-migrationen falder derfor tilbage til en bar `unsignedBigInteger` uden
 * constraint. Det er en TAVS degradering: intet fejler, men referentiel
 * integritet mangler permanent, medmindre nogen opdager det.
 *
 * Denne migration koerer SIDST og lukker hullet uanset hvornaar vaerten
 * oprettede sine egne tabeller. Create-migrationerne bliver dermed
 * raekkefoelge-UAFHAENGIGE — det er pointen.
 *
 * 🪤 Idempotent i begge retninger: den springer over hvis constraint'en
 * allerede findes (create-migrationen naaede at saette den), og hvis
 * vaertstabellen stadig ikke findes. Sidstnaevnte LOGGES frem for at tie —
 * en manglende FK skal kunne opdages uden at nogen laeser skemaet.
 */
return new class extends Migration
{
    public function up(): void
    {
        $p = $this->prefix();

        $this->addForeignKey(
            $p.'questionnaires',
            'company_id',
            $this->tableFor('company'),
            $p.'q_company_fk',
        );

        $this->addForeignKey(
            $p.'questionnaire_responses',
            'subject_id',
            $this->tableFor('subject'),
            $p.'qr_subject_fk',
        );

        $this->addForeignKey(
            $p.'questionnaire_responses',
            'completed_by',
            $this->tableFor('user'),
            $p.'qr_completed_by_fk',
        );
    }

    public function down(): void
    {
        $p = $this->prefix();

        // 🪤 Drop KUN de navne vi selv satte. up() springer over naar vaerten
        // allerede har noeglen, saa vores navn findes ikke paa en frisk
        // installation — et ubetinget drop gav der:
        //   SQLSTATE[42000] 1091: Can't DROP 'qe_q_company_fk'
        // Fejlen er BETINGET: paa opgraderingsstien satte up() alle tre selv,
        // og rollback var groen. Derfor gemte den sig. Maalt 24-08-2026.
        foreach ([
            [$p.'questionnaires', $p.'q_company_fk'],
            [$p.'questionnaire_responses', $p.'qr_subject_fk'],
            [$p.'questionnaire_responses', $p.'qr_completed_by_fk'],
        ] as [$table, $name]) {
            if (! Schema::hasTable($table) || ! $this->constraintExists($table, $name)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($name) {
                $blueprint->dropForeign($name);
            });
        }
    }

    private function addForeignKey(
        string $table,
        string $column,
        string $referencedTable,
        string $constraintName,
    ): void {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        if (! Schema::hasTable($referencedTable)) {
            // Bevidst LOG frem for tavshed: uden vaertstabellen kan noeglen ikke
            // saettes, og den manglende integritet skal vaere synlig.
            logger()->warning(
                "[questionnaire] Kunne ikke saette fremmednoegle {$table}.{$column} -> {$referencedTable}: tabellen findes ikke."
            );

            return;
        }

        // 🪤 Tjek KOLONNEN, ikke constraint-NAVNET. Vaertsprojektet kan have
        // sat den samme noegle under sit eget navn (Frankston-master goer det i
        // 2026_04_13_add_missing_foreign_keys), og et navne-tjek ville ikke se
        // den ⇒ to identiske constraints paa samme kolonne. Maalt 24-08-2026.
        if ($this->columnPointsAt($table, $column, $referencedTable)) {
            return;
        }

        // 🪤 ALTID cascadeOnDelete — samme semantik som create-migrationerne.
        // Foerste udgave brugte nullOnDelete for company_id, saa to
        // installationer af SAMME pakke fik forskellig adfaerd ved sletning af
        // et team: enten forsvandt spoergeskemaerne, eller company_id blev
        // NULL. Create-migrationen er den etablerede adfaerd og den produktion
        // koerer paa; denne migration skal rette sig efter den.
        Schema::table($table, function (Blueprint $blueprint) use ($column, $referencedTable, $constraintName) {
            $blueprint->foreign($column, $constraintName)->references('id')->on($referencedTable)->cascadeOnDelete();
        });
    }

    /**
     * Peger kolonnen allerede paa den RIGTIGE tabel?
     *
     * 🪤 Tjekker baade kolonne OG maaltabel. En tidligere udgave spurgte kun
     * "har kolonnen en FK?" — den sprang saa over selvom noeglen pegede paa en
     * FORKERT tabel, og efterlod fejlen permanent. Praecis den tavse
     * degradering denne migration findes for at fjerne.
     *
     * 🪤 Kraever ogsaa at noeglen er ENKELT-kolonne: en sammensat noegle der
     * tilfaeldigvis indeholder kolonnen er ikke den noegle vi vil saette.
     */
    private function columnPointsAt(string $table, string $column, string $referencedTable): bool
    {
        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if (($foreignKey['columns'] ?? []) !== [$column]) {
                continue;
            }

            if (($foreignKey['foreign_table'] ?? null) === $referencedTable) {
                return true;
            }
        }

        return false;
    }

    private function constraintExists(string $table, string $constraintName): bool
    {
        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if (($foreignKey['name'] ?? null) === $constraintName) {
                return true;
            }
        }

        return false;
    }

    private function tableFor(string $model): string
    {
        return (new (config("questionnaire.models.{$model}")))->getTable();
    }

    private function prefix(): string
    {
        return config('questionnaire.table_prefix', 'qe_');
    }
};
