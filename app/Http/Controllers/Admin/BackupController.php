<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class BackupController extends Controller
{
    public function __construct(private DatabaseBackupService $backups)
    {
    }

    public function index()
    {
        return view('admin.backups.index', ['backups' => $this->backups->list()]);
    }

    public function store(): RedirectResponse
    {
        try {
            $this->backups->create();
            return back()->with('success', __('app.backup_created'));
        } catch (\Throwable $e) {
            return back()->with('error', __('app.backup_failed') . ': ' . $e->getMessage());
        }
    }

    public function download(string $filename): Response
    {
        // basename() strips any directory traversal attempt (../) from
        // the filename before it ever touches the filesystem path.
        $safe = basename($filename);
        $path = $this->backups->path($safe);

        abort_unless(file_exists($path) && Str::endsWith($safe, '.sql'), 404);

        return response()->download($path);
    }

    public function destroy(string $filename): RedirectResponse
    {
        $this->backups->delete(basename($filename));

        return back()->with('success', __('app.backup_deleted'));
    }
}
