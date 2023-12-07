<?php

namespace App\Contracts;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface RzdDocumentServiceInterface
{

    public function generateActs(UploadedFile $tableFile): string;

    public function forgetOldDocuments(): void;

}