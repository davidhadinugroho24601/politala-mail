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

        $parser = new Parser();
        $pdf = $parser->parseFile($fullPath);
        $text = $pdf->getText();

        $mails = Mail::whereNotNull('hidden_code')->get();

        foreach ($mails as $mail) {
            if (str_contains($text, $mail->hidden_code)) {
                $this->verificationResult = "✅ Verifikasi berhasil! Dokumen dengan subjek \"{$mail->subject}\" ditemukan.";
                $this->record = $mail; // Simpan record yang cocok
                return;
            }
        }

        $this->verificationResult = "❌ PDF does not contain any hidden message from records.";
        $this->record = null;
    }
}
