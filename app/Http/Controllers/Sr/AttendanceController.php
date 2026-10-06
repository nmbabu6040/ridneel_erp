<?php

namespace App\Http\Controllers\Sr;

use App\Http\Controllers\Controller;
use App\Models\SrAttendance;
use App\Models\User;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Face-match distance threshold. face-api.js's own docs recommend
     * ~0.6 for its 128-d descriptors — below this the two faces are
     * considered "the same person". Kept as one constant so it's easy
     * to tune from one place if false-rejects/accepts show up in the field.
     */
    private const MATCH_THRESHOLD = 0.6;

    /** SR's own check-in/check-out screen for today. */
    public function index(Request $request)
    {
        $user = $request->user();

        $today = SrAttendance::firstOrNew([
            'user_id' => $user->id,
            'date'    => now()->toDateString(),
        ]);

        $recentHistory = SrAttendance::where('user_id', $user->id)
            ->where('date', '<', now()->toDateString())
            ->latest('date')
            ->limit(10)
            ->get();

        return view('sr.attendance', [
            'today'   => $today,
            'history' => $recentHistory,
            'profile' => $user->faceProfile,
        ]);
    }

    public function checkIn(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->ensureEnrolled($user);

        $data = $this->validateAttendancePayload($request);

        $attendance = SrAttendance::firstOrNew([
            'user_id' => $user->id,
            'date'    => now()->toDateString(),
        ]);

        if ($attendance->exists && $attendance->check_in_at) {
            return back()->with('error', __('app.already_checked_in'));
        }

        if ($data['distance'] > self::MATCH_THRESHOLD) {
            return back()->with('error', __('app.face_mismatch'));
        }

        $photoPath = FaceController::savePhoto($data['photo'], 'checkin');

        $isLate = now()->format('H:i') > '10:00';

        $attendance->fill([
            'check_in_at'             => now(),
            'check_in_lat'            => $data['lat'],
            'check_in_lng'            => $data['lng'],
            'check_in_photo'          => $photoPath,
            'check_in_match_distance' => $data['distance'],
            'status'                  => $isLate ? 'late' : 'present',
        ])->save();

        if ($isLate) {
            NotificationDispatcher::send(
                eventKey: 'sr_late_checkin',
                subject: __('app.email_sr_late_subject') . ' ' . $user->name,
                view: 'emails.sr-late-checkin',
                data: [
                    'srName'      => $user->name,
                    'checkInTime' => now()->format('h:i A'),
                    'date'        => now()->format('d M Y'),
                ],
                defaultRecipients: NotificationDispatcher::emailsWithPermission('sr.tracking.view')
            );
        }

        return redirect()->route('sr.attendance.index')->with('success', __('app.checked_in_success'));
    }

    public function checkOut(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->ensureEnrolled($user);

        $data = $this->validateAttendancePayload($request);

        $attendance = SrAttendance::where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();

        if (!$attendance || !$attendance->check_in_at) {
            return back()->with('error', __('app.not_checked_in_yet'));
        }

        if ($attendance->check_out_at) {
            return back()->with('error', __('app.already_checked_out'));
        }

        if ($data['distance'] > self::MATCH_THRESHOLD) {
            return back()->with('error', __('app.face_mismatch'));
        }

        $photoPath = FaceController::savePhoto($data['photo'], 'checkout');

        $attendance->fill([
            'check_out_at'             => now(),
            'check_out_lat'            => $data['lat'],
            'check_out_lng'            => $data['lng'],
            'check_out_photo'          => $photoPath,
            'check_out_match_distance' => $data['distance'],
        ])->save();

        return redirect()->route('sr.attendance.index')->with('success', __('app.checked_out_success'));
    }

    /** Manager-facing attendance report across all SR users. */
    public function report(Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        $srUserIds = User::whereHas('role', fn ($q) => $q->where('slug', 'sr'))->pluck('id');

        $records = SrAttendance::with('user')
            ->whereIn('user_id', $srUserIds)
            ->where('date', $date)
            ->get()
            ->keyBy('user_id');

        $srUsers = User::whereIn('id', $srUserIds)->orderBy('name')->get();

        return view('sr.attendance-report', compact('date', 'srUsers', 'records'));
    }

    private function ensureEnrolled($user): void
    {
        abort_unless($user->faceProfile, 422, __('app.enroll_face_first'));
    }

    private function validateAttendancePayload(Request $request): array
    {
        $data = $request->validate([
            'lat'      => ['required', 'numeric', 'between:-90,90'],
            'lng'      => ['required', 'numeric', 'between:-180,180'],
            'photo'    => ['required', 'string'],
            'distance' => ['required', 'numeric', 'min:0'],
        ]);

        return $data;
    }
}
