<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    /**
     * Danh sách users
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('hoten', 'like', '%' . $request->search . '%')
                    ->orWhere('tendangnhap', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('tendangnhap')->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Form tạo user mới
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Lưu user mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tendangnhap' => 'required|unique:taikhoan,tendangnhap|max:50|regex:/^[a-zA-Z0-9_]+$/',
            'matkhau' => 'required|min:6',
            'hoten' => 'required|max:50'
        ], [
            'tendangnhap.required' => 'Vui lòng nhập tên đăng nhập',
            'tendangnhap.unique' => 'Tên đăng nhập đã tồn tại',
            'tendangnhap.regex' => 'Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới',
            'matkhau.required' => 'Vui lòng nhập mật khẩu',
            'matkhau.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            'hoten.required' => 'Vui lòng nhập họ tên'
        ]);

        try {
            User::create([
                'tendangnhap' => $validated['tendangnhap'],
                'matkhau' => md5($validated['matkhau']),
                'hoten' => $validated['hoten']
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', 'Tạo tài khoản thành công');

        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Form sửa user
     */
    public function edit($tendangnhap)
    {
        $user = User::findOrFail($tendangnhap);
        return view('admin.users.edit', compact('user'));
    }


    /**
     * Cập nhật user
     */
    public function update(Request $request, $tendangnhap)
    {
        $user = User::findOrFail($tendangnhap);

        $validated = $request->validate([
            'hoten' => 'required|max:50',
            'matkhau' => 'nullable|min:6',
            'email' => 'nullable|email|max:100',
            'sodienthoai' => 'nullable|regex:/^[0-9]{9,15}$/',
            'trangthai' => 'required|in:1,0'
        ], [
            'hoten.required' => 'Vui lòng nhập họ tên',
            'matkhau.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            'email.email' => 'Email không hợp lệ',
            'sodienthoai.regex' => 'Số điện thoại không hợp lệ',
            'trangthai.required' => 'Vui lòng chọn trạng thái',
            'trangthai.in' => 'Trạng thái không hợp lệ'
        ]);

        try {
            $user->hoten = $validated['hoten'];
            $user->email = $validated['email'] ?? null;
            $user->sodienthoai = $validated['sodienthoai'] ?? null;
            $user->trangthai = $validated['trangthai'];

            if (!empty($validated['matkhau'])) {
                $user->matkhau = md5($validated['matkhau']);
            }

            $user->save();

            return redirect()->route('admin.users.index')
                ->with('success', 'Cập nhật tài khoản thành công');

        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Xóa user
     */
    public function destroy($tendangnhap)
    {
        $user = User::findOrFail($tendangnhap);

        // Không cho phép xóa chính mình
        if ($user->tendangnhap === auth()->user()->tendangnhap) {
            return back()->with('error', 'Không thể xóa tài khoản của chính mình');
        }

        try {
            $user->delete();
            return redirect()->route('admin.users.index')
                ->with('success', 'Xóa tài khoản thành công');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}