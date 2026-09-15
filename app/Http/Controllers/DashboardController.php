<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Trash\Auth\Facades\Auth;
use Trash\Filesystem\Facades\Storage;
use Trash\Http\Message\ServerRequest;
use Trash\Http\RedirectResponse;
use Trash\Support\Str;
use Trash\View\View;

class DashboardController
{
     public function index(): View
    {
        return view('dashboard.index', ['user' => Auth::user()]);
    }

    public function updateAvatar(ServerRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $file = $request->file('avatar');
        if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
            return redirect()->back()->withErrors(['avatar' => ['Please choose a valid image file.']]);
        }
        $ext = pathinfo($file->getClientFilename() ?? 'img', PATHINFO_EXTENSION);
        $path = 'avatars/' . $user->id . '_' . Str::random(16) . '.' . $ext;
        Storage::disk()->put($path, (string) $file->getStream());
        if ($user->avatar !== null && $user->avatar !== '') {
            Storage::disk()->delete($user->avatar);
        }
        $user->update(['avatar' => $path]);
        return redirect()->route('dashboard.index')->with('status', 'Avatar updated.');
    }
}
