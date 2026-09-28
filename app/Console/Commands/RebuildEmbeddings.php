<?php

namespace App\Console\Commands;

use App\Jobs\UpdateCompanyEmbedding;
use App\Jobs\UpdateProductEmbedding;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Console\Command;

class RebuildEmbeddings extends Command
{
    protected $signature = 'embeddings:rebuild';
    protected $description = 'Recalcule les vecteurs de tous les produits et commerces';

    public function handle(): int
    {
        Product::query()->pluck('id')->each(fn ($id) => UpdateProductEmbedding::dispatch($id));
        Company::query()->pluck('id')->each(fn ($id) => UpdateCompanyEmbedding::dispatch($id));

        $this->info('Jobs lancés.');

        return self::SUCCESS;
    }
}