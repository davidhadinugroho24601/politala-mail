<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReceivedMailsResource\Pages;
use App\Filament\Resources\ReceivedMailsResource\RelationManagers\AttachmentMailRelationManager;
use App\Filament\Resources\ReceivedMailsResource\RelationManagers;
use App\Models\Mail;
use App\Models\Group;
use App\Models\User;
use App\Notifications\ApprovalProcessed;
use App\Models\GroupDetailsView;
use App\Models\ApprovalChain;
use App\Models\MailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\ListRecords;
// use Filament\Forms\Components\Actions;
use Filament\Support\Facades\Filament;
use Filament\Forms\Components\Actions;
// use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\View;
use App\Services\MailService;
use Filament\Navigation\NavigationItem;
use Filament\Tables\Actions\BulkAction;
// use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use ZipArchive;


class ReceivedMailsResource extends Resource
{
    protected static ?string $model = Mail::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Inbox';

    protected static ?string $modelLabel = 'Inbox';
    
    protected static ?string $navigationGroup = 'Inbox';

    protected function mount(int|string $record): void
    {
        parent::mount($record);
    
        $approval = ApprovalChain::where('mail_id', $record->id)
            ->where('group_id', session('groupID'))
            ->where('status', 'waiting')
            ->where('authority', 'approve')
            ->first();
    
        if ($approval) {
            $approval->update(['status' => 'approved']);
        }
    }
    
    
    public static function getNavigationItems(): array
    {
        // Get distinct template names
        $templates = Mail::with('template')->get()->pluck('template.name')->unique()->filter();
    
        $navigationItems = [];
        $navigationGroup = 'Surat Masuk';

        // Default "All Mails" navigation
        $navigationItems[] = NavigationItem::make()
            ->label('Semua surat')
            ->url(static::getUrl('index'))
            ->icon('heroicon-o-inbox')
            ->group($navigationGroup)
            ;
    
        // Generate a navigation item for each template name
        foreach ($templates as $templateName) {
            
            $navigationItems[] = NavigationItem::make()
                ->label($templateName)
                ->url(static::getUrl('index') . '?template=' . urlencode($templateName))
                ->icon('heroicon-o-document-text')
                ->group($navigationGroup);

        }
    
        return $navigationItems;
    }
    public static function canCreate(): bool
    {
        return false;
    }
    public static function getFormActions(): array
{
    return [];
}

    protected static ?string $pluralModelLabel = 'Inbox';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Approval Timeline')
                    ->schema([
                        View::make('filament.tables.columns.timeline-widget')
                            ->label(false)
                            ->columnSpanFull(),
                    ]),
                Select::make('is_staged')
                    ->required()
                    ->live()
                    ->label('Tipe Surat')
                    ->options([
                        'yes' => 'Berjenjang',
                        'no' => 'Langsung',
                    ])
                    ->disabled(),
    
                Select::make('final_id')
                    ->label('Jabatan Penerima')
                    ->options(Group::pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->disabled(),
    
                Select::make('direct_id')
                    ->label('Penerima')
                    ->options(User::pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->hidden(fn ($get) => !$get('final_id') || $get('is_staged') !== 'no')
                    ->disabled(),
    
                TextInput::make('subject')
                    ->required()
                    ->disabled(),
    
                Forms\Components\Select::make('template_id')
                    ->label('Template')
                    ->options(MailTemplate::pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->disabled(),
    
                View::make('components.google-docs-editor')
                    ->label('Google Docs Editor')
                    ->columnSpan('full')
                    ->disabled()
                    ->hidden(fn (string $context): bool => $context !== 'edit')
                    ->extraAttributes(['style' => 'width: 100%; height: 600px; border: none;']),
    
                Forms\Components\Select::make('group_id')
                    ->label('Pengirim')
                    ->options(Group::pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->disabled()
                    ->default(session('groupID'))
                    ->dehydrated(),
            ]);
    }
    
        
    

  
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('assignedCode.code') -> label('Kode Surat'),
                TextColumn::make('subject'),
                TextColumn::make('writer.name') -> label('Pengirim'),
                TextColumn::make('group.name') -> label('Jabatan Pengirim'),
                // Tables\Columns\TextColumn::make('actions')
                // ->label('Actions')
                // ->formatStateUsing(fn ($record) => '
                //     <a href="#" onclick="alert(\'ID: '.$record->id.'\')" class="px-2 py-1 bg-blue-500 text-white rounded">
                //         Click Me
                //     </a>
                // ')
                // ->html(),

            ])
            ->filters([
                //
            ])
            ->actions([

                // Action::make('debug')
                // ->label('Debug ID')
                // ->button()
                // ->color('danger')
                // ->record(function ($record) {
                //     // dd($record);
                //     return $record; // Ensure this returns the correct record
                // })
                // ->action(fn ($record) => dd($record->id)),
                
                Tables\Actions\EditAction::make()
                ->label(fn ($record) => 
                    ($approval = ApprovalChain::where('mail_id', $record->id)
                        ->where('group_id', session('groupID'))
                        ->first()) && $approval->status === 'waiting' && $approval->authority === 'approve' 
                        ? 'Edit' 
                        : 'View'
                )
                ->icon(fn ($record) => 
                    ($approval = ApprovalChain::where('mail_id', $record->id)
                        ->where('group_id', session('groupID'))
                        ->first()) && $approval->status === 'waiting' && $approval->authority === 'approve' 
                        ? 'heroicon-o-pencil' 
                        : 'heroicon-o-eye'
                )
                ->modalHeading(fn ($record) => 
                    ($approval = ApprovalChain::where('mail_id', $record->id)
                        ->where('group_id', session('groupID'))
                        ->first()) && $approval->status === 'waiting' && $approval->authority === 'approve' 
                        ? 'Edit Record' 
                        : 'View Record'
                )
                ->form(fn ($record) => 
                ($approval = ApprovalChain::where('mail_id', $record->id)
                    ->where('group_id', session('groupID'))
                    ->first()) && $approval->status === 'waiting' && $approval->authority === 'approve' 
                    ? [/* Your editable form fields */] 
                    : [] // No form fields for "View" mode
            )
            ->color(function ($record) {
                $approval = ApprovalChain::where('mail_id', $record->id)
                    ->where('group_id', session('groupID'))
                    ->first();
            
                return $approval && $approval->status === 'waiting' && $approval->authority === 'read' 
                    ? 'primary' 
                    : 'secondary';
            })
            
            ,

              
                Action::make('approveMail')
                ->label('Setujui')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->action(function ($record, MailService $mailService) {
                    $mailService->approveMail($record);
                })
                ->color('success')
                ->extraAttributes(fn ($record) => 
                    ($approval = ApprovalChain::where('mail_id', $record->id)
                        ->where('group_id', session('groupID'))
                        ->first()) && $approval->status === 'waiting' && $approval->authority === 'approve' 
                        ? [] // Tampilkan tombol
                        : ['style' => 'display: none;'] // Sembunyikan tombol jika tidak memenuhi syarat
                ),

                Action::make('declineWithNote')
                ->label('Revisi')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    Textarea::make('notes')
                        ->label('Tambahkan Catatan')
                        ->required(),
                ])
                ->action(function ($record, array $data, MailService $mailService) {
                    $mailService->declineMailWithNote($record, $data);
                })
                ->requiresConfirmation()
                ->color('warning')
                ->extraAttributes(fn ($record) => 
                    ($approval = ApprovalChain::where('mail_id', $record->id)
                        ->where('group_id', session('groupID'))
                        ->first()) && $approval->status === 'waiting' && $approval->authority === 'approve' 
                        ? [] // Tampilkan tombol
                        : ['style' => 'display: none;'] // Sembunyikan tombol jika tidak memenuhi syarat
                ),
            
            
                Action::make('decline')
                ->label('Tolak')
                ->icon('heroicon-o-x-circle')
                ->action(function ($record, MailService $mailService) {
                    $mailService->declineMail($record);
                })
                ->requiresConfirmation()
                ->color('danger')
                ->extraAttributes(fn ($record) => 
                    ($approval = ApprovalChain::where('mail_id', $record->id)
                        ->where('group_id', session('groupID'))
                        ->first()) && $approval->status === 'waiting' && $approval->authority === 'approve' 
                        ? [] // Tampilkan tombol
                        : ['style' => 'display: none;'] // Sembunyikan tombol jika tidak memenuhi syarat
                ),
            
                
            

            
            ])
            ->bulkActions([
                BulkAction::make('Download files')
                    ->action(function ($records) {
                        // Collect all the pdf paths, ignoring null values
                        $pdfPaths = $records->pluck('pdf_path')->filter()->toArray();
            
                        if (empty($pdfPaths)) {
                            // If no PDFs are selected, return early
                            return;
                        }
            
                        // Create a temporary zip file
                        $zip = new ZipArchive;
                        $zipFileName = 'documents_' . now()->timestamp . '.zip';
                        $zipFilePath = storage_path('app/public/' . $zipFileName);
            
                        // Open the zip file for writing
                        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
                            foreach ($pdfPaths as $pdfPath) {
                                // Add each PDF file to the zip
                                $fileName = basename($pdfPath); // Get the file name
                                $zip->addFile($pdfPath, $fileName);
                            }
            
                            // Close the zip file
                            $zip->close();
            
                            // Return the zip file as a download
                            return response()->download($zipFilePath)->deleteFileAfterSend(true);
                        }
                    })
                    ->requiresConfirmation()
                    ->icon('heroicon-m-arrow-down-tray'),
            ]);
            
    }
   
    public static function getRelations(): array
    {
        return [
            AttachmentMailRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReceivedMails::route('/'),
            'create' => Pages\CreateReceivedMails::route('/create'),
            'edit' => Pages\EditReceivedMails::route('/{record}/edit'),
            // 'approveMail' => Pages\ApproveMail::route('/{record}/approve'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $groupId = session('groupID');
        if (session('groupID') !== 'admin') {
        // Return empty query if session does not have a group ID
        if (!$groupId) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }
    
        $query = parent::getEloquentQuery()->withoutGlobalScopes();
    
        // Get approved mail IDs for the current group
        $approvedMailIds = ApprovalChain::where('group_id', $groupId)
            ->where('status', 'Approved')
            ->pluck('mail_id')
            ->toArray();
    
        $query->where(function ($q) use ($groupId, $approvedMailIds) {
            $q->where('target_id', $groupId)
              ->where('status', 'Submitted');
    
            if (!empty($approvedMailIds)) {
                $q->orWhereIn('id', $approvedMailIds);
            }
        });
    
        // Apply filtering by template name if a parameter exists
        if ($templateName = request()->query('template')) {
            $query->whereHas('template', function ($q) use ($templateName) {
                $q->where('name', $templateName);
            });
        }
    }
    else{
        $query = parent::getEloquentQuery();
         // Apply filtering by template name if a parameter exists
         if ($templateName = request()->query('template')) {
            $query->whereHas('template', function ($q) use ($templateName) {
                $q->where('name', $templateName);
            });
        }
    }
        return $query;
    }
    
    

}
