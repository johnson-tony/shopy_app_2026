<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerLocationController extends Controller
{
    /**
     * Broadcast the partner's live GPS coordinates.
     */
    public function update(Request $request): JsonResponse
    {
        $partner = auth('partner')->user();

        $validated = $request->validate([
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy'  => ['nullable', 'numeric'],
        ]);

        $partner->updateLocation(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            isset($validated['accuracy']) ? (float) $validated['accuracy'] : null
        );

        return response()->json([
            'success'   => true,
            'message'   => 'Live location broadcast successfully',
            'latitude'  => $partner->latitude,
            'longitude' => $partner->longitude,
            'updated_at'=> $partner->last_location_at?->toIso8601String(),
        ]);
    }
}
