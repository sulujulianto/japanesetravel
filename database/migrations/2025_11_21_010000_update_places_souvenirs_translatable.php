<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $places = DB::table('places')->select('id', 'name', 'description')->get();
            foreach ($places as $place) {
                DB::table('places')
                    ->where('id', $place->id)
                    ->update([
                        'name' => json_encode([
                            'id' => $place->name,
                            'en' => $place->name,
                        ]),
                        'description' => $place->description
                            ? json_encode(['id' => $place->description, 'en' => $place->description])
                            : null,
                    ]);
            }

            $souvenirs = DB::table('souvenirs')->select('id', 'name', 'description')->get();
            foreach ($souvenirs as $souvenir) {
                DB::table('souvenirs')
                    ->where('id', $souvenir->id)
                    ->update([
                        'name' => json_encode([
                            'id' => $souvenir->name,
                            'en' => $souvenir->name,
                        ]),
                        'description' => $souvenir->description
                            ? json_encode(['id' => $souvenir->description, 'en' => $souvenir->description])
                            : null,
                    ]);
            }

            return;
        }

        Schema::table('places', function (Blueprint $table) {
            $table->json('name_json')->nullable();
            $table->json('description_json')->nullable();
        });

        DB::table('places')->update([
            'name_json' => DB::raw("JSON_OBJECT('id', name, 'en', name)"),
            'description_json' => DB::raw("CASE WHEN description IS NULL THEN NULL ELSE JSON_OBJECT('id', description, 'en', description) END"),
        ]);

        DB::statement('ALTER TABLE places DROP COLUMN name');
        DB::statement('ALTER TABLE places DROP COLUMN description');
        DB::statement('ALTER TABLE places CHANGE name_json name JSON NOT NULL');
        DB::statement('ALTER TABLE places CHANGE description_json description JSON NULL');

        Schema::table('souvenirs', function (Blueprint $table) {
            $table->json('name_json')->nullable();
            $table->json('description_json')->nullable();
        });

        DB::table('souvenirs')->update([
            'name_json' => DB::raw("JSON_OBJECT('id', name, 'en', name)"),
            'description_json' => DB::raw("CASE WHEN description IS NULL THEN NULL ELSE JSON_OBJECT('id', description, 'en', description) END"),
        ]);

        DB::statement('ALTER TABLE souvenirs DROP COLUMN name');
        DB::statement('ALTER TABLE souvenirs DROP COLUMN description');
        DB::statement('ALTER TABLE souvenirs CHANGE name_json name JSON NOT NULL');
        DB::statement('ALTER TABLE souvenirs CHANGE description_json description JSON NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $places = DB::table('places')->select('id', 'name', 'description')->get();
            foreach ($places as $place) {
                $name = json_decode($place->name ?? '', true);
                $description = json_decode($place->description ?? '', true);

                DB::table('places')
                    ->where('id', $place->id)
                    ->update([
                        'name' => is_array($name) ? ($name['id'] ?? '') : $place->name,
                        'description' => is_array($description) ? ($description['id'] ?? null) : $place->description,
                    ]);
            }

            $souvenirs = DB::table('souvenirs')->select('id', 'name', 'description')->get();
            foreach ($souvenirs as $souvenir) {
                $name = json_decode($souvenir->name ?? '', true);
                $description = json_decode($souvenir->description ?? '', true);

                DB::table('souvenirs')
                    ->where('id', $souvenir->id)
                    ->update([
                        'name' => is_array($name) ? ($name['id'] ?? '') : $souvenir->name,
                        'description' => is_array($description) ? ($description['id'] ?? null) : $souvenir->description,
                    ]);
            }

            return;
        }

        Schema::table('places', function (Blueprint $table) {
            $table->string('name_text', 150)->nullable();
            $table->text('description_text')->nullable();
        });

        DB::table('places')->update([
            'name_text' => DB::raw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"id\"'))"),
            'description_text' => DB::raw("JSON_UNQUOTE(JSON_EXTRACT(description, '$.\"id\"'))"),
        ]);

        DB::statement('ALTER TABLE places DROP COLUMN name');
        DB::statement('ALTER TABLE places DROP COLUMN description');
        DB::statement('ALTER TABLE places CHANGE name_text name VARCHAR(150) NOT NULL');
        DB::statement('ALTER TABLE places CHANGE description_text description TEXT NULL');

        Schema::table('souvenirs', function (Blueprint $table) {
            $table->string('name_text')->nullable();
            $table->text('description_text')->nullable();
        });

        DB::table('souvenirs')->update([
            'name_text' => DB::raw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"id\"'))"),
            'description_text' => DB::raw("JSON_UNQUOTE(JSON_EXTRACT(description, '$.\"id\"'))"),
        ]);

        DB::statement('ALTER TABLE souvenirs DROP COLUMN name');
        DB::statement('ALTER TABLE souvenirs DROP COLUMN description');
        DB::statement('ALTER TABLE souvenirs CHANGE name_text name VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE souvenirs CHANGE description_text description TEXT NULL');
    }
};
