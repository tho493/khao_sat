@extends('layouts.admin')

@section('title', 'Tạo đợt khảo sát')

@section('content')
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dot-khao-sat.index') }}">Đợt khảo sát</a></li>
                <li class="breadcrumb-item active">Tạo mới</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Tạo đợt khảo sát mới</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.dot-khao-sat.store') }}" enctype="multipart/form-data">
                            @csrf

                                <div class="mb-3">
                                    <label for="image" class="form-label">Ảnh đại diện (Banner)</label>
                                    <input class="form-control @error('image') is-invalid @enderror" type="file" id="image" name="image"
                                        accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Tùy chọn. Ảnh nên có tỷ lệ 16:9 hoặc 4:3. Tối đa 2MB.</small>
                                </div>

                            <div class="mb-3">
                                <label class="form-label">Tên đợt khảo sát <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('ten_dot') is-invalid @enderror" 
                                       name="ten_dot" value="{{ old('ten_dot') }}" 
                                       placeholder="VD: Khảo sát sinh viên học kỳ 1 năm 2024-2025" required>
                                @error('ten_dot')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mẫu khảo sát <span class="text-danger">*</span></label>
                                <select class="form-select @error('mau_khaosat_id') is-invalid @enderror" 
                                        name="mau_khaosat_id" required>
                                    <option value="">-- Chọn mẫu khảo sát --</option>
                                    @foreach($mauKhaoSats as $mau)
                                        <option value="{{ $mau->id }}" 
                                            {{ old('mau_khaosat_id', request('mau_khaosat_id')) == $mau->id ? 'selected' : '' }}>
                                            {{ $mau->ten_mau }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('mau_khaosat_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Năm học <span class="text-danger">*</span></label>
                                <select class="form-select @error('namhoc_id') is-invalid @enderror" 
                                        name="namhoc_id" required>
                                    <option value="">-- Chọn năm học --</option>
                                    @foreach($namHocs as $nh)
                                        <option value="{{ $nh->id }}" 
                                            {{ old('namhoc_id') == $nh->id ? 'selected' : '' }}>
                                            {{ $nh->namhoc }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('namhoc_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('tungay') is-invalid @enderror" 
                                           name="tungay" value="{{ old('tungay') }}" required>
                                    @error('tungay')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('denngay') is-invalid @enderror" 
                                           name="denngay" value="{{ old('denngay') }}" required>
                                    @error('denngay')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea class="form-control @error('mota') is-invalid @enderror" 
                                          name="mota" rows="3" 
                                          placeholder="Mô tả về đợt khảo sát...">{{ old('mota') }}</textarea>
                                @error('mota')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                </div>

                                <div class="text-end">
                                    <a href="{{ route('admin.dot-khao-sat.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Hủy
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Tạo đợt khảo sát
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow">
                        <div class="card-body">
                            <h6 class="card-title">Hướng dẫn</h6>
                            <ul class="mb-0">
                                <li>Chọn mẫu khảo sát đã được tạo sẵn</li>
                                <li>Đợt khảo sát sẽ ở trạng thái "Nháp" sau khi tạo</li>
                                <li>Cần kích hoạt đợt khảo sát để bắt đầu thu thập phản hồi</li>
                                <li>Không thể sửa mẫu khảo sát sau khi đợt đã kích hoạt</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection