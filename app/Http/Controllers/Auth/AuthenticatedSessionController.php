<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPathFor($request->user()));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Semua role CMS (admin/editor/author) diarahkan ke dashboard,
     * karena ketiganya sekarang punya akses fungsional ke /admin.
     * Hanya pembaca tanpa role CMS yang akan tetap ke blog (jaga-jaga
     * kalau nanti ada role tambahan di luar 3 ini).
     */
    protected function redirectPathFor($user): string
    {
        return in_array($user->role, ['admin', 'editor', 'author'])
            ? route('admin.posts.index')
            : route('blog.index');
    }
}