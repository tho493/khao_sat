<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataSource;
use App\Models\DataSourceValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DataSourceController extends Controller
{
    public function index(Request $request)
    {
        $query = DataSource::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        }

        $dataSources = $query->withCount('values')->orderBy('name')->paginate(15);
        return view('admin.data-source.index', compact('dataSources'));
    }

    public function create()
    {
        return view('admin.data-source.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $count = 1;
        while (DataSource::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $dataSource = DataSource::create([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

        return redirect()->route('admin.data-source.edit', $dataSource)->with('success', 'Tạo nguồn dữ liệu thành công. Bây giờ bạn có thể thêm các giá trị.');
    }

    public function edit(DataSource $dataSource)
    {
        $dataSource->load('values');
        return view('admin.data-source.edit', compact('dataSource'));
    }

    public function update(Request $request, DataSource $dataSource)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $dataSource->update($validated);

        return redirect()->route('admin.data-source.edit', $dataSource)->with('success', 'Cập nhật nguồn dữ liệu thành công.');
    }

    public function destroy(DataSource $dataSource)
    {
        if ($dataSource->questions()->count() > 0) {
            return back()->with('error', 'Không thể xóa. Nguồn dữ liệu này đang được sử dụng bởi các câu hỏi khảo sát.');
        }

        $dataSource->delete();
        return redirect()->route('admin.data-source.index')->with('success', 'Xóa nguồn dữ liệu thành công.');
    }

    public function storeValue(Request $request, DataSource $dataSource)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        $dataSource->values()->create($validated);

        return back()->with('success', 'Thêm giá trị thành công.');
    }

    public function updateValue(Request $request, DataSourceValue $value)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        $value->update($validated);

        return back()->with('success', 'Cập nhật giá trị thành công.');
    }

    public function destroyValue(DataSourceValue $value)
    {
        $value->delete();
        return back()->with('success', 'Xóa giá trị thành công.');
    }
}
