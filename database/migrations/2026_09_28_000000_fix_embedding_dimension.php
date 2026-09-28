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
        DB::statement('DROP INDEX IF EXISTS products_embedding_idx');
        DB::statement('ALTER TABLE products DROP COLUMN IF EXISTS embedding');
        DB::statement('ALTER TABLE products ADD COLUMN embedding vector(768)');
        DB::statement('CREATE INDEX products_embedding_idx ON products USING hnsw (embedding vector_cosine_ops)');

        DB::statement('DROP INDEX IF EXISTS companies_embedding_idx');
        DB::statement('ALTER TABLE companies DROP COLUMN IF EXISTS embedding');
        DB::statement('ALTER TABLE companies ADD COLUMN embedding vector(768)');
        DB::statement('CREATE INDEX companies_embedding_idx ON companies USING hnsw (embedding vector_cosine_ops)');
    }

    public function down(): void {}
};