<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReceivedMailsResource\Pages;
use App\Filament\Resources\ReceivedMailsResource\RelationManagers;
use App\Models\Mail;
use App\Models\ApprovalChain;
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

class ReceivedMailsResource extends Resource
{
    protected static ?string $model = Mail::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Inbox';

    protected static ?string $modelLabel = 'Inbox';

    protected static ?string $pluralModelLabel = 'Inbox';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('approveMail')
                ->label('Approve')
                ->icon('heroicon-o-envelope')
                ->action(function ($record) {
                    $groupID = session('groupID'); // Get the current group ID
                    
                    $currentApproval = ApprovalChain::where('mail_id', $record->id)
                    ->where('group_id', $groupID)->orderBy('id') // Ensure it's in order
                    ->first();
                    // Update the approval status for the current group
                   
            
                    // Find the next approval step
                    $nextApproval = ApprovalChain::where('mail_id', $record->id)
                        ->where('id', '>', $currentApproval->id) // Get the next group
                        ->orderBy('id') // Ensure it's in order
                        ->first();
            // dd($nextApproval);

            $currentApproval ->update(['status' => 'approved']);
                    if ($nextApproval) {
                        // Update target_id to the next approval step
                        $record->update(['target_id' => $nextApproval->group_id]);
                    } else {
                        // If no next step, mark as finished
                        ApprovalChain::where('mail_id', $record->id)
                            ->where('group_id', $groupID)
                            ->update(['status' => 'finished']);
                    }
            
                    Notification::make()
                        ->title('Approval processed successfully!')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->color('success')
                ->hidden(fn ($record) => 
                    ApprovalChain::where('mail_id', $record->id)
                        ->where('group_id', session('groupID'))
                        ->value('status') !== 'waiting' // Hide if already approved/declined
            ),
            
            Action::make('declineWithNote')
            ->label('Tolak') // Button label
            ->icon('heroicon-o-x-circle') // Optional: Add an icon
            ->form([
                Textarea::make('notes')
                    ->label('Tambahkan Catatan')
                    ->required(),
            ])
            ->action(function ($record, array $data) {
                // Update the ApprovalChain with the user's notes and set status to 'denied'
                ApprovalChain::where('mail_id', $record->id)
                    ->where('group_id', session('groupID'))
                    ->update([
                        'status' => 'denied',
                        'notes' => $data['notes'],
                    ]);
        
                // Change mail status back to "Draft"
                $record->update(['status' => 'Draft']);
        
                // Send a success notification
                Notification::make()
                    ->title('Mail declined with notes successfully! Status set to Draft.')
                    ->success()
                    ->send();
            })
            ->requiresConfirmation()
            ->color('danger')
            ->hidden(fn ($record) => 
                ApprovalChain::where('mail_id', $record->id)
                    ->where('group_id', session('groupID'))
                    ->value('status') !== 'waiting' // Hide if already approved/declined
        ),
        
            
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReceivedMails::route('/'),
            'create' => Pages\CreateReceivedMails::route('/create'),
            'edit' => Pages\EditReceivedMails::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes();
    
        // Check if there is any target mail for the current group
        $query->where(function ($q) {
            $q->where('target_id', session('groupID'))
              ->where('status', 'Submitted');
        });
    
        // Get all approved mail IDs for this group
        $approvedMailIds = ApprovalChain::where('group_id', session('groupID'))
            ->where('status', 'Approved')
            ->pluck('mail_id'); // Get only mail_id column values
    
        // If there are approved mails, add them to the filter
        if ($approvedMailIds->isNotEmpty()) {
            $query->orWhereIn('id', $approvedMailIds);
        }
    
        return $query;
    }
    

}
