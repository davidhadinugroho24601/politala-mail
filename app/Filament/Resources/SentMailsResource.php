<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SentMailsResource\Pages;
use App\Filament\Resources\SentMailsResource\RelationManagers;
use App\Models\Mail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Group;
use App\Models\User;
use App\Models\Disposition;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\RichEditor;

use Filament\Forms\Components\TextInput;
use Filament\Panel;
use App\Http\Middleware\CheckGroupIDSession;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Carbon;
use Filament\Tables\Columns\ViewColumn;
use App\Models\ApprovalChain;
use App\Models\MailTemplate;
use App\Filament\Resources\SentMailsResource\RelationManagers\AttachmentMailRelationManager;
use Filament\Forms\Components\View;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\HtmlString;
use App\Services\MailService;
use Filament\Navigation\NavigationItem;



class SentMailsResource extends BaseResource
{
    protected static ?string $model = Mail::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    

            public static function getNavigationItems(): array
            {
                $navigationGroup = 'Kirim Surat';

                $statuses = Mail::select('status')->distinct()->pluck('status')->toArray();
                $navigationItems = [
                    NavigationItem::make('Semua surat')
                        ->url(static::getUrl()) // No query parameters needed for "All Mails"
                        ->icon('heroicon-o-inbox')
                        ->group($navigationGroup)
                        ,
                ];
            
                foreach ($statuses as $status) {
                    $navigationItems[] = NavigationItem::make(ucwords($status))
                        ->url(route('filament.admin.resources.sent-mails.index', ['status' => $status])) // Use `route()`
                        ->icon(static::getStatusIcon($status))
                        ->group($navigationGroup)
                        ;
                }
            
                return $navigationItems;
            }
            
            public static function canCreate(): bool
            {
                // Ambil status surat terbaru dari database
                $latestMailStatus = Mail::latest()->value('status');
              
                // Jika status terbaru adalah "Submitted", return false
                if (request('status') === 'Submitted') {
                    return false;
                }
            
                return true;
            }
            
            protected static function getStatusIcon(string $status): string
            {
                return match ($status) {
                    'Draft' => 'heroicon-o-pencil', // Icon for drafts
                    'Submitted' => 'heroicon-o-paper-airplane', // Icon for submitted mails
                    default => 'heroicon-o-document-text', // Fallback icon
                };
            }
            
        

    public static function form(Form $form): Form
    {

        return $form
        ->schema([
            Forms\Components\Section::make('Approval Timeline')
                ->schema([
                    View::make('filament.tables.columns.timeline-widget')
                        ->label(false) // Hides default label
            ->columnSpanFull()
            ,
                ]),
              

           

        

               
                                
            

                Forms\Components\Select::make('template_id')
                ->label('Template')
                ->options(function () {
                    $groupId = session('groupID'); // Get the logged-in user's group ID
                    $divisionId = \App\Models\Group::where('id', $groupId)->value('division_id'); // Get division ID
                    return MailTemplate::whereHas('mailPath', function ($query) use ($groupId) {
                        $query->where('sender_id', $groupId); // Filter by division
                    })->pluck('name', 'id');
                })
                ->searchable()
                ->required()
                ->live()
                ->disabled(fn ($record) => $record !== null)
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    if (!$state) return;
                
                    $template = MailTemplate::find($state);
                    if (!$template) return;
                
                    $set('is_staged', $template->type === 'direct' ? 'no' : 'yes');
                
                    $groupID = session('groupID') ?? 0;
                    $path = $template->mailPath?->where('sender_id', $groupID)?->first();
                    
                    $set('final_id', $path?->receiver_id);
                })
                ,
               
                TextInput::make('subject')
                ->required()
                ->disabled(fn ($record) => $record && $record->status !== 'Draft'),
            
                Forms\Components\Select::make('is_staged')
                ->label('Tipe Surat')
                ->required()
                ->options([
                    'yes' => 'Berjenjang',
                    'no' => 'Langsung',
                ])
                
                ->disabled()
                ->dehydrated()
            , // Disable it so users can't manually change it
                
                Forms\Components\Select::make('final_id')
                ->label('Jabatan Penerima')
                ->options(fn () => Group::pluck('name', 'id'))
                ->searchable()
                ->required()
                ->live()
                ->disabled()
                ->dehydrated()
            ,
            
            
                Select::make('direct_id')
                    ->label('Penerima')
                    ->options(fn (callable $get, callable $set) => 
                        ($finalId = $get('final_id')) 
                            ? \App\Models\User::whereHas('groupDetailsView', fn ($query) => $query->where('group_id', $finalId))
                                ->pluck('name', 'id')
                            : []
                    )
                    ->searchable()
                    ->required()
                    ->hidden(fn ($get) => !$get('final_id') || $get('is_staged') !== 'no') // Disable if no final_id or is_staged == 'yes',
                    ->disabled(fn ($record) => $record !== null),
                 
                // Forms\Components\Select::make('template_id')
                // ->label('Template')
                // ->options(function () {
                //     $groupId = session('groupID'); // Get the logged-in user's group ID from session
                //     $divisionId = \App\Models\Group::where('id', $groupId)->value('division_id'); // Get division ID
            
                //     if (!$divisionId) {
                //         return []; // No division found, return empty options
                //     }
            
                //     return MailTemplate::whereHas('templateAvailability', function ($query) use ($divisionId) {
                //         $query->where('division_id', $divisionId); // Filter by division
                //     })->pluck('name', 'id');
                // })
                // ->searchable()
                // ->required()
                // ->live()
                // ->disabled(fn ($record) => $record !== null),
            

                Select::make('disposition_id') 
                ->label('Disposisi')
                ->options(Disposition::pluck('name', 'id'))
                ->searchable()
                ->required()
                ->hidden(fn ($get) => \App\Models\MailTemplate::where('id', $get('template_id'))->value('name') !== 'Disposisi'),
            
            
            

            View::make('components.google-docs-editor')
            ->label('Google Docs Editor')
            ->columnSpan('full')
            ->disabled()
            ->hidden(fn (string $context): bool => $context !== 'edit')
            ->extraAttributes(['style' => 'width: 100%; height: 600px; border: none;']),

            Forms\Components\Select::make('group_id')
            ->label('Pengirim')
            ->options(
                Group::where('id', session('groupID'))->pluck('name', 'id') // Filter options by session groupID
            )
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
            Tables\Columns\TextColumn::make('finalTarget.name')
            ->label('Receiver')
            ->sortable()
            ->searchable(),
            Tables\Columns\TextColumn::make('subject')->label('Subjek'),
            TextColumn::make('created_at')
            ->label('Terakhir Diubah')
            ->formatStateUsing(function ($state, $record) {
                // Use updated_at if not null, otherwise fallback to created_at
                $timestamp = $record->updated_at ?? $record->created_at;

                return $timestamp
                    ? Carbon::parse($timestamp)
                        ->timezone('Asia/Makassar') // Set WITA timezone
                        ->translatedFormat('d F Y, H:i') . ' WITA'
                    : '-'; // Show a dash if no timestamps are available
            }),
         
            // ViewColumn::make('timeline')
            // ->label('Timeline')
            // ->view('filament.tables.columns.timeline-widget', [
            //     'approvals' => ApprovalChain::orderBy('id')
            //         ->get()
            //         ->map(function ($approval) {
            //             // Dynamically add a color based on the status
            //             $approval->color = match ($approval->status) {
            //                 'waiting' => '#f39c12', // Yellow for waiting
            //                 'approved' => '#2ecc71', // Green for approved
            //                 'finished' => '#2ecc71', // Green for finished
            //                 'denied' => '#e74c3c', // Red for denied
            //                 default => '#bdc3c7', // Grey for any other status
            //             };
            //             return $approval;
            //         }),
            // ])
            // ->extraAttributes(['class' => 'max-w-xs overflow-hidden truncate', 'style' => 'text-align: left;'])


        ])
        
        ->filters([
           
        ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->label(fn ($record) => $record->status === 'Submitted' ? 'View' : 'Edit') // Change label based on status
                ->icon(fn ($record) => $record->status === 'Submitted' ? 'heroicon-o-eye' : 'heroicon-o-pencil') // Change icon
                ->color(fn ($record) => $record->status === 'Submitted' ? 'secondary' : 'primary') // Set blue for "View"
                ->tooltip(fn ($record) => $record->status === 'Submitted' 
                    ? 'View' 
                    : 'Edit'),
                    

                    Action::make('sendMail')
                        ->label('Send Mail')
                        ->icon('heroicon-o-paper-airplane')
                        ->action(function ($record, MailService $mailService) {
                            $mailService->sendMail($record);
                        })
                        ->requiresConfirmation()
                        ->color('success')
                        ->hidden(fn ($record) => $record->status === 'Submitted'),



                Tables\Actions\Action::make('viewNotes')
                ->label('Catatan')
                ->icon('heroicon-o-envelope')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading(fn ($record) => 'Catatan dari ' . collect($record->rejecter())->pluck('name')->implode(', '))
                ->modalDescription(fn ($record) => $record->notes)
                ->modalIcon('heroicon-o-envelope')
                ->modalSubmitActionLabel('Close')
                ->modalCancelAction(false) // Removes the Cancel button
            
              
                ->hidden(fn ($record) => empty($record->notes)),
                
            

            

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListSentMails::route('/'),
            'create' => Pages\CreateSentMails::route('/create'),
            'edit' => Pages\EditSentMails::route('/{record}/edit'),
        ];
    }

    public static function getRouteMiddleware(Panel $panel): array
    {
        // Apply the middleware to the UserResource routes
        return [
            'checkGroupID' => CheckGroupIDSession::class,
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        
        $query = parent::getEloquentQuery()
            ->where('group_id', session('groupID'))
            ->where('writer_id', auth()->id());
    
        if ($status = request('status')) {
            $query->where('status', $status);
        }
    
        return $query;
    }
    
}
