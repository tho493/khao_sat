<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataSource;
use App\Models\DataSourceValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

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

        $dataSources = $query->withCount('values')
            ->with(['questions:id,data_source_id,mau_khaosat_id'])
            ->orderBy('name')
            ->paginate(15);
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
        $this->clearCacheForDataSource($dataSource);

        return redirect()->route('admin.data-source.edit', $dataSource)->with('success', 'Cập nhật nguồn dữ liệu thành công.');
    }

    public function destroy(DataSource $dataSource)
    {
        if ($dataSource->questions()->count() > 0) {
            return back()->with('error', 'Không thể xóa. Nguồn dữ liệu này đang được sử dụng bởi các câu hỏi khảo sát.');
        }

        $this->clearCacheForDataSource($dataSource);
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
        $this->clearCacheForDataSource($dataSource);

        return back()->with('success', 'Thêm giá trị thành công.');
    }

    public function updateValue(Request $request, DataSourceValue $value)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        $value->update($validated);
        $this->clearCacheForDataSource($value->dataSource);

        return back()->with('success', 'Cập nhật giá trị thành công.');
    }

    public function destroyValue(DataSourceValue $value)
    {
        $dataSource = $value->dataSource;
        $value->delete();
        $this->clearCacheForDataSource($dataSource);
        return back()->with('success', 'Xóa giá trị thành công.');
    }

    /**
     * Xóa cache các đợt khảo sát sử dụng nguồn dữ liệu này
     */
    private function clearCacheForDataSource($dataSource)
    {
        if ($dataSource) {
            $mauKhaoSatIds = \App\Models\CauHoiKhaoSat::where('data_source_id', $dataSource->id)
                ->pluck('mau_khaosat_id')
                ->unique();

            if ($mauKhaoSatIds->isNotEmpty()) {
                $dotKhaoSatIds = \App\Models\DotKhaoSat::whereIn('mau_khaosat_id', $mauKhaoSatIds)
                    ->pluck('id');

                foreach ($dotKhaoSatIds as $dotId) {
                    Cache::forget('survey_detail_' . $dotId);
                }
                Cache::forget('survey_active_dots');
            }
        }
    }
}
