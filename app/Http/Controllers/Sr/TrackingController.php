<?php

namespace App\Http\Controllers\Sr;

use App\Http\Controllers\Controller;
use App\Models\SrAttendance;
use App\Models\SrLocation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackingController extends Controller
{
    /**
     * Called every ~90s by public/js/sr-tracking.js while an SR is
     * checked in and has the attendance page open. Kept as a tiny JSON
     * endpoint (not a full page) so it's cheap to call frequently.
     */
    public function ping(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat'      => ['required', 'numeric', 'between:-90,90'],
            'lng'      => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric'],
        ]);

        SrLocation::create([
            'user_id'         => $request->user()->id,
            'latitude'        => $data['lat'],
            'longitude'       => $data['lng'],
            'accuracy_meters' => $data['accuracy'] ?? null,
            'recorded_at'     => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    /** Manager-facing live map — latest known position of every SR. */
    public function liveMap()
    {
        $srUserIds = User::whereHas('role', fn ($q) => $q->where('slug', 'sr'))->pluck('id');

        // One row per SR: their single most recent location ping.
        $latestIds = SrLocation::whereIn('user_id', $srUserIds)
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy('user_id')
            ->pluck('id');

        $locations = SrLocation::with('user')->whereIn('id', $latestIds)->get();

        $todayAttendance = SrAttendance::whereIn('user_id', $srUserIds)
            ->where('date', now()->toDateString())
            ->get()
            ->keyBy('user_id');

        return view('sr.live-map', compact('locations', 'todayAttendance'));
    }
}
