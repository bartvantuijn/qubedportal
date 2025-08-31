<?php

namespace App\Http\Controllers;

use App\Models\License;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseApiController extends Controller
{
    public function validateLicense(Request $request): JsonResponse
    {
        // Valideer de inkomende request
        $request->validate([
            'key' => 'required|string',
            'domain' => 'required|string',
        ]);

        // Zoek de licentie in de database
        $license = License::where('key', $request->input('key'))->first();

        if (! $license) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Licentie niet gevonden.',
            ], 404);
        }

        // Controleer of het domein overeenkomt
        if ($license->domain !== $request->input('domain')) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Domein komt niet overeen.',
            ], 403);
        }

        // Controleer of de licentie niet verlopen is
        if ($license->expires_at !== null && $license->expires_at->lt(Carbon::now())) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Licentie is verlopen.',
            ], 403);
        }

        // Licentie is geldig
        $license->update(['verified_at' => Carbon::now()]);

        return response()->json([
            'status' => 'valid',
            'message' => 'Licentie is geldig.',
        ]);
    }
}
