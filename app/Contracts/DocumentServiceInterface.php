<?php

namespace App\Contracts;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface DocumentServiceInterface
{

    public function generateActs(UploadedFile $tableFile): string;

}