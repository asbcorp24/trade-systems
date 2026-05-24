<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::orderBy('created_at', 'desc')->paginate(50);

        return view('notifications.index', compact('notifications'));
    }

    public function user()
    {
        $notifications = Notification::orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('notifications.user', compact('notifications'));
    }

    public function markAllRead()
    {
        Notification::where('is_read', false)->update(['is_read' => true]);
        return redirect()->back();
    }

    public function settings()
    {
        $user = Auth::user();
        return view('notifications.settings', compact('user'));
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'telegram_subscribed' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $user->telegram_subscribed = $request->telegram_subscribed ? 1 : 0;
        $user->save();

        return redirect()->back()->with('success', 'Настройки сохранены.');
    }
}
