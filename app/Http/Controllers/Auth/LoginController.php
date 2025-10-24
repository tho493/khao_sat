<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Rules\Recaptcha;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'tendangnhap' => 'required',
                'matkhau' => 'required',
                'g-recaptcha-response' => ['required', new Recaptcha],
            ], [
                'g-recaptcha-response.required' => 'Vui lòng xác thực bạn không phải là robot.'
            ]);

            $user = User::where('tendangnhap', $validated['tendangnhap'])
                ->where('matkhau', md5($validated['matkhau']))
                ->first();

            if ($user) {
                if (Schema::hasColumn('taikhoan', 'last_login')) {
                    $user->last_login = now();
                    $user->save();
                }

                Auth::login($user);
                $request->session()->regenerate();
                return redirect()->intended('admin');
            }

            throw ValidationException::withMessages([
                'tendangnhap' => 'Thông tin đăng nhập không chính xác.',
            ]);

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())
                ->withInput($request->except('matkhau'));
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}