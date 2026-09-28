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
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        DB::statement('CREATE INDEX IF NOT EXISTS products_title_trgm_idx ON products USING GIN (title gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS products_keywords_trgm_idx ON products USING GIN (keywords gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS products_description_trgm_idx ON products USING GIN (description gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS companies_name_trgm_idx ON companies USING GIN (name gin_trgm_ops)');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        DB::statement('DROP INDEX IF EXISTS products_title_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS products_keywords_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS products_description_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS companies_name_trgm_idx');
    }
};