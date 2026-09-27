<?php

namespace App\Jobs;

use App\Models\Company;
use App\Services\EmbeddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class UpdateCompanyEmbedding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $companyId) {}

    public function handle(EmbeddingService $embedding): void
    {
        $company = Company::find($this->companyId);

        if (!$company) {
            return;
        }

        $text = trim($company->name.' '.$company->description);
        $imageUrl = $company->card_image_url ?? $company->cover_image_url;

        $vector = $embedding->embed($text, $imageUrl);

        if (!$vector) {
            return;
        }

        DB::update(
            'UPDATE companies SET embedding = ? WHERE id = ?',
            ['['.implode(',', $vector).']', $company->id]
        );
    }
}