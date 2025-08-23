<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use App\Models\Mail;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Str;
class VerifyPdf extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static string $view = 'filament.pages.verify-pdf';
    protected static ?string $navigationLabel = 'Verifikasi surat';

    public ?array $data = [];
    public ?string $verificationResult = null;
    public ?Mail $record = null; // Tambahkan record Mail yang cocok 


    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            FileUpload::make('pdf')
                ->label('Upload PDF')
                ->disk('public')
                ->directory('uploads')
                ->acceptedFileTypes(['application/pdf'])
                ->required()
                ->reactive(),
        ])->statePath('data');
    }




public function verify(): void
{
    $filePath = $this->form->getState()['pdf'] ?? null;

    if (!$filePath) {
        $this->verificationResult = "❌ No file uploaded.";
        $this->record = null;
        return;
    }

    $fullPath = Storage::disk('public')->path($filePath);

    if (!file_exists($fullPath)) {
        $this->verificationResult = "❌ File not found.";
        $this->record = null;
        return;
    }

    // Recompute hash of uploaded file
    $uploadedHash = hash_file('sha256', $fullPath);

    // Find record with same hash
    $mail = Mail::where('file_hash', $uploadedHash)->first();

    if ($mail) {
        $this->verificationResult = "✅ Verifikasi berhasil! Dokumen dengan subjek \"{$mail->subject}\" ditemukan.";
        $this->record = $mail;
        return;
    }

    $this->verificationResult = "❌ Dokumen tidak valid atau sudah dimodifikasi.";
    $this->record = null;
}


}
