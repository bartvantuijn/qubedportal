<?php

use App\Http\Controllers\LicenseApiController;
use Illuminate\Support\Facades\Route;

Route::post('/license/validate', [LicenseApiController::class, 'validateLicense']);
