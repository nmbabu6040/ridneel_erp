<?php

namespace App\Http\Controllers\Sr;

use App\Http\Controllers\Controller;
use App\Models\SrFaceProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FaceController extends Controller
{
    /** Enrollment / re-enrollment screen — one reference face per user. */
    public function create(Request $request)
    {
        $profile = $request->user()->faceProfile;

        return view('sr.face-enroll', compact('profile'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'descriptor'   => ['required', 'array', 'size:128'],
            'descriptor.*' => ['numeric'],
            'photo'        => ['required', 'string'], // base64 data URI from the camera capture
        ]);

        $photoPath = $this->savePhoto($data['photo'], 'enroll');

        SrFaceProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'descriptor'  => $data['descriptor'],
                'photo_path'  => $photoPath,
                'enrolled_at' => now(),
            ]
        );

        return redirect()->route('sr.attendance.index')->with('success', __('app.face_enrolled'));
    }

    /**
     * Decode a base64 data-URI photo from the browser and store it under
     * the public disk. Using the public disk (not a signed/private one)
     * because managers viewing the live attendance report need to see
     * the thumbnail directly — see README for the storage:link step.
     */
    public static function savePhoto(string $base64, string $folder): string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
            $base64    = substr($base64, strpos($base64, ',') + 1);
        } else {
            $extension = 'jpg';
        }

        $binary   = base64_decode($base64);
        $filename = "sr-attendance/{$folder}/" . now()->format('Y/m/d') . '/' . Str::random(20) . '.' . $extension;

        Storage::disk('public')->put($filename, $binary);

        return $filename;
    }
}
