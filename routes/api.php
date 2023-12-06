<?php

use App\Http\Controllers\GenerateDocumentController;
use Illuminate\Support\Facades\Route;

Route::post('generate-documents', [GenerateDocumentController::class, 'generateActs']);

