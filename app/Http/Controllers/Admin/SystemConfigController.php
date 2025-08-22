<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CauHinhHeThong;
use App\Models\TemplateEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class SystemConfigController extends Controller
{
    public function index()
    {
        $emailTemplates = TemplateEmail::all();

        return view('admin.config.index', compact('emailTemplates'));
    }

    //template email
    public function storeEmailTemplate(Request $request)
    {
        $validated = $request->validate([
            'ten_template' => 'required|string|max:255',
            'ma_template' => 'required|string|max:50|unique:template_email,ma_template|regex:/^[a-z0-9_]+$/',
            'tieude' => 'required|string|max:255',
            'noidung' => 'required|string',
            'bien_template' => 'nullable|string'
        ]);

        if (!empty($validated['bien_template'])) {
            $validated['bien_template'] = array_map('trim', explode(',', $validated['bien_template']));
        }

        TemplateEmail::create($validated);

        return back()->with('success', 'Thêm template email mới thành công.');
    }

    // Cập nhật một template email đã có.
    public function updateEmailTemplate(Request $request, TemplateEmail $template)
    {
        $validated = $request->validate([
            'ten_template' => 'required|string|max:255',
            'tieude' => 'required|string|max:255',
            'noidung' => 'required|string',
            'bien_template' => 'nullable|string'
        ]);

        if (!empty($validated['bien_template'])) {
            $validated['bien_template'] = array_map('trim', explode(',', $validated['bien_template']));
        } else {
            $validated['bien_template'] = null;
        }

        $template->update($validated);

        return back()->with('success', 'Cập nhật template email thành công.');
    }

    // Xóa một template email.
    public function destroyEmailTemplate(TemplateEmail $template)
    {
        $template->delete();
        return back()->with('success', 'Đã xóa template email: ' . $template->ten_template);
    }

    public function testEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'template_id' => 'required|exists:template_email,id'
        ]);

        try {
            return response()->json(['success' => true, 'message' => 'Email test đã được gửi']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}