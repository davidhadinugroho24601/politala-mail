<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class DatabaseBackup extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-server-stack';
    protected static string $view = 'filament.pages.database-backup';
    protected static ?string $navigationLabel = 'Backup Database';
    public static function shouldRegisterNavigation(): bool
    {
        // Return the value from the config (defaulting to false if not set)
        return config('filament.navigation_enabled', false);
    }
    public ?string $backupFileName = '';

    public function mount(): void
    {
        $this->backupFileName = 'database_backup_' . now()->format('Y-m-d_H-i-s'); // Default filename
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('backupFileName')
                ->label('Backup File Name')
                ->required()
                ->placeholder('Enter backup filename...')
                ->default($this->backupFileName)
                ->suffix('.sql'),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportDatabase')
                ->label('Export Database')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn () => $this->exportDatabase())
                ->requiresConfirmation()->hidden(),
        ];
    }

    public function exportDatabase()
    {
        $dbName = env('DB_DATABASE', 'mail-app'); // Get database name from .env
        $filename = trim($this->backupFileName) ?: 'database_backup_' . now()->format('Y-m-d_H-i-s');
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename) . '.sql'; // Ensure safe filename
        $filepath = storage_path('app/public/' . $filename);

        $tables = DB::select("
            SELECT TABLE_NAME as table_name FROM information_schema.tables 
            WHERE table_schema = ? AND table_type = 'BASE TABLE'
        ", [$dbName]);

        $sqlDump = "-- Database backup for $dbName\n\n";

        foreach ($tables as $table) {
            $tableName = $table->table_name;

            // Get CREATE TABLE statement
            $createTable = DB::selectOne("SHOW CREATE TABLE `$tableName`");
            $createSql = $createTable->{'Create Table'} ?? null;

            if ($createSql) {
                $sqlDump .= $createSql . ";\n\n";
            }

            // Get table data
            $rows = DB::table($tableName)->get();
            if ($rows->isNotEmpty()) {
                $sqlDump .= "INSERT INTO `$tableName` VALUES\n";
                $values = [];
                foreach ($rows as $row) {
                    $values[] = "(" . implode(',', array_map(fn($val) => $val === null ? 'NULL' : "'" . addslashes($val) . "'", (array) $row)) . ")";
                }
                $sqlDump .= implode(",\n", $values) . ";\n\n";
            }
        }

        // Save SQL dump to file with user-defined filename
        Storage::disk('public')->put($filename, $sqlDump);

        return Response::download(storage_path('app/public/' . $filename))->deleteFileAfterSend(true);
    } 
}
