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

class SentMailsResource extends BaseResource
{
    protected static ?string $model = Mail::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    


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
            // Final Target ID - Dropdown populated with user names
                Select::make('final_id')
                ->label('Penerima')
                ->options(Group::pluck('name', 'id'))
                ->searchable()
                ->required() 
                ->disabled(fn ($record) => $record !== null),


                TextInput::make('subject')
            ->required()
            ->disabled(fn ($record) => $record && $record->status !== 'Draft'),
                            
                Select::make('is_staged')
            ->required()
            ->label('Tipe Surat')
                ->options([
                    'yes' => 'Berjenjang',
                    'no' => 'Langsung',
                ])                
                ->disabled(fn ($record) => $record !== null),


            Forms\Components\Select::make('template_id')
            ->label('Template')
            ->options(
                MailTemplate::pluck('name', 'id') // Filter options by session groupID
            )
            ->searchable()
            ->required()
            ->disabled(fn ($record) => $record !== null),

            
            RichEditor::make('content')
            ->label('Mail Content')
            ->columnSpan('full')
            ->extraAttributes(['style' => 'word-wrap: break-word;'])
            ->hidden(fn (string $context): bool => $context !== 'edit')
            ->disabled(fn ($record) => $record && $record->status !== 'Draft')
            ->afterStateUpdated(function ($state, $record) {
                if (!$record) {
                    return;
                }
        
                // Extract image URLs from the content
                preg_match_all('/<img[^>]+src="([^">]+)"/', $state, $matches);
        
                foreach ($matches[1] as $url) {
                    $record->addMediaFromUrl($url)
                        ->usingFileName('attachments/' . uniqid() . '.jpg') // Customize the path and filename
                        ->toMediaCollection('attachments');
                }
            }),
        
            

            Forms\Components\Select::make('group_id')
            ->label('Group')
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
                Action::make('sendMail') // The action's name
                ->label('Send Mail') // Button label
                ->icon('heroicon-o-envelope') // Optional: Add an icon
                ->action(function ($record) { // Define the logic when the action is triggered
                    // Your custom logic here
                    // Example: Mark the mail as "Sent"
                    $record->update(['status' => 'Submitted']);
                    if(!$record->released){
                        $record->update(['released' => true]);
                        $firstReport = Report::first();

                        if ($firstReport) {
                            $firstReport->increment('created_mails');
                        } else {
                            $firstReport = Report::create(['created_mails' => 1]);
                        }
                        
                    }
                    
                   

                // Reset all "denied" approvals back to "waiting"
                ApprovalChain::where('mail_id', $record->id)
                    ->where('status', 'denied')
                    ->update(['status' => 'waiting']);

                    Notification::make()
                        ->title('Mail sent successfully!')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation() // Optional: Add a confirmation dialog
                ->color('success')
                ->hidden(fn ($record) => $record->status === 'Submitted'), // Optional: Define the button color
       

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
    
    // Use getTableQuery to filter data based on session
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('group_id', session('groupID'))->where('writer_id', auth()->id());
    }
}
