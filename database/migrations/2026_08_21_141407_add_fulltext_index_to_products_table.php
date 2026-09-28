<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        DB::statement("
            ALTER TABLE products
            ADD COLUMN search_vector tsvector
            GENERATED ALWAYS AS (
                to_tsvector('french', coalesce(title, '') || ' ' || coalesce(keywords, '') || ' ' || coalesce(description, ''))
            ) STORED
        ");

        DB::statement("CREATE INDEX products_search_vector_idx ON products USING GIN (search_vector)");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        DB::statement("DROP INDEX IF EXISTS products_search_vector_idx");
        DB::statement("ALTER TABLE products DROP COLUMN IF EXISTS search_vector");
    }
};