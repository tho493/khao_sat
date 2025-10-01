<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\NamHoc;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Tạo tài khoản admin nếu database trống
        User::firstOrCreate([
            'tendangnhap' => 'admin',
            'matkhau' => md5('123456'),
            'hoten' => 'Administrator',
            'email' => 'admin@admin.com'
        ]);

        // Tạo năm học
        NamHoc::create(['namhoc' => '2026-2027']);
        NamHoc::create(['namhoc' => '2027-2028']);
        NamHoc::create(['namhoc' => '2028-2029']);
    }

}