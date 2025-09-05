<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatbotQa;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ChatbotQa::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('keywords', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $faqs = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.faq.index', compact('faqs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'keywords' => 'required|string|max:255',
            'question' => 'nullable|string|max:255',
            'answer' => 'required|string',
            'is_enabled' => 'boolean',
        ]);

        $validated['is_enabled'] = $request->has('is_enabled');

        ChatbotQa::create($validated);

        return redirect()->route('admin.faq.index')->with('success', 'Thêm câu hỏi FAQ thành công.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChatbotQa $faq)
    {
        return view('admin.faq.edit', compact('faq'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChatbotQa $faq)
    {
        $validated = $request->validate([
            'keywords' => 'required|string|max:255',
            'question' => 'nullable|string|max:255',
            'answer' => 'required|string',
            'is_enabled' => 'boolean',
        ]);

        $validated['is_enabled'] = $request->has('is_enabled');

        $faq->update($validated);

        return redirect()->route('admin.faq.index')->with('success', 'Cập nhật câu hỏi FAQ thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChatbotQa $faq)
    {
        $faq->delete();
        return redirect()->route('admin.faq.index')->with('success', 'Xóa câu hỏi FAQ thành công.');
    }
}