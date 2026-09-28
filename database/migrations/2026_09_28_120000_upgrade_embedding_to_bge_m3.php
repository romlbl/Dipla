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

        foreach (['products', 'companies'] as $table) {
            DB::statement("DROP INDEX IF EXISTS {$table}_embedding_idx");
            DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS embedding");
            DB::statement("ALTER TABLE {$table} ADD COLUMN embedding vector(1024)");
            DB::statement("CREATE INDEX {$table}_embedding_idx ON {$table} USING hnsw (embedding vector_cosine_ops)");
        }
    }

    public function down(): void {}
};