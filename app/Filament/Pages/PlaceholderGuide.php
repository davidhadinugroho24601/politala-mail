<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class PlaceholderGuide extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-information-circle';
    protected static string $view = 'filament.pages.placeholder-guide';
    // protected static ?string $navigationGroup = 'Mailing';
    protected static ?string $navigationLabel = 'Panduan Variabel Surat';

    public array $placeholders = [
        '{kode surat}' => 'Kode unik surat',
        '{surat terbit}' => 'Jumlah surat terbit',
        '{nama pengirim}' => 'Nama pengirim (dari sesi)',
        '{nama penerima}' => 'Nama penerima (dari rekaman)',
        '{jabatan pengirim}' => 'Jabatan pengirim',
        '{jabatan penerima}' => 'Jabatan penerima',
        '{NIP Pengirim}' => 'NIP pengirim (Nomor Induk Pegawai)',
        '{NIP Penerima}' => 'NIP penerima',
        '{NIDN Pengirim}' => 'NIDN pengirim (Nomor Induk Dosen Nasional)',
        '{NIDN Penerima}' => 'NIDN penerima',
        '{akronim divisi}' => 'Akronim divisi',
        '{tanggal}' => 'Tanggal saat ini',
        '{bulan}' => 'Bulan saat ini',
        '{tahun}' => 'Tahun saat ini',
    ];
}
