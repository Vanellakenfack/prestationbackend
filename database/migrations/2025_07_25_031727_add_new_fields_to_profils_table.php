    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::table('profils', function (Blueprint $table) {
                // Ajoutez vos nouveaux champs ici
                $table->text('bio')->nullable()->after('video'); // Exemple: après la colonne 'video'
                $table->string('website')->nullable()->after('bio');
                $table->decimal('hourly_rate', 8, 2)->nullable()->after('website'); // 8 chiffres au total, 2 après la virgule
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::table('profils', function (Blueprint $table) {
                // Supprimez les champs dans l'ordre inverse de leur ajout
                $table->dropColumn(['bio', 'website', 'hourly_rate']);
            });
        }
    };

    