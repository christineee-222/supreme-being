<?php

namespace App\Http\Controllers\Moderation;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AdminModeratorController extends Controller
{
    public function index(): Response
    {
        abort_unless(request()->user()?->isAdmin(), 403);

        $moderators = User::where('role', 'moderator')
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Moderators/Index', [
            'moderators' => $moderators,
        ]);
    }
}
