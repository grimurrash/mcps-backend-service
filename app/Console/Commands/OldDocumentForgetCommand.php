<?php

namespace App\Console\Commands;

use App\Contracts\RzdDocumentServiceInterface;
use Illuminate\Console\Command;

class OldDocumentForgetCommand extends Command
{
    protected $signature = 'generate-document:forget';

    protected $description = 'Удаление старых выгрузок и временных файлов';

    public function handle(RzdDocumentServiceInterface $rzdDocumentService): void
    {
        $rzdDocumentService->forgetOldDocuments();
    }
}
