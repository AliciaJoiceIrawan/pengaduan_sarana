<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Membuat Akun Admin
        User::create([
            'nama' => 'Admin',
            'email' => 'admin@sekolah.test',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Membuat Akun Siswa
        User::create([
            'nama' => 'joice',
            'email' => 'siswa@sekolah.test',
            'password' => Hash::make('123456'),
            'role' => 'siswa',
            'nis' => '123456',
            'kelas' => 'XII RPL 1',
        ]);

        // Membuat Kategori Pengaduan
        foreach(['Ruang Kelas', 'Toilet', 'Laboratorium', 'Lapangan'] as $nama){
            Category::create(['nama' => $nama]);
        }
    }
}