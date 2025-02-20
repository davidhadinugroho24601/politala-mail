<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReceivedMailsResource\Pages;
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
use Illuminate\Support\Facades\Auth;
// use Filament\Forms\Components\Actions;
use Filament\Support\Facades\Filament;
use Filament\Forms\Components\Actions;
// use Filament\Forms\Components\Actions\Action;
class ReceivedMailsResource extends BaseResource
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
                // TextInput::make('subject')->required(),

               
           
    //             Actions::make([
    //                 Actions\Action::make('debug')
    // ->label('Debug ID')
    // ->button()
    // ->color('danger')
    // ->action(function ($record) {
    //     // Dump the entire route parameters
    //     dd(request()->route()->parameters());
    // })
                // ]),
                    // Approve Button
                    // Actions\Action::make('approveMail')
                    //     ->label('Approve')
                    //     ->icon('heroicon-o-envelope')
                    //     ->color('success')
                    //     ->requiresConfirmation()
                    //     ->action(function ($livewire, $data) {
                    //         $record = $livewire->record; // Get the current record
                    //         $groupID = session('groupID'); // Get the current group ID
                    //         // dd($record->id);
                    //         $currentApproval = ApprovalChain::where('mail_id', $record->id)
                    //             ->where('group_id', $groupID)
                    //             ->orderBy('id') // Ensure it's in order
                    //             ->first();
    
                    //         if (!$currentApproval) {
                    //             Notification::make()
                    //                 ->title('Error')
                    //                 ->danger()
                    //                 ->body('Approval step not found.')
                    //                 ->send();
                    //             return;
                    //         }
    
                    //         // Update the approval status for the current group
                    //         $currentApproval->update(['status' => 'approved']);
    
                    //         // Find the next approval step
                    //         $nextApproval = ApprovalChain::where('mail_id', $record->id)
                    //             ->where('id', '>', $currentApproval->id)
                    //             ->orderBy('id')
                    //             ->first();
    
                    //         if ($nextApproval) {
                    //             // Update target_id to the next approval step
                    //             $record->update(['target_id' => $nextApproval->group_id]);
                    //         } else {
                    //             // If no next step, mark as finished
                    //             ApprovalChain::where('mail_id', $record->id)
                    //                 ->where('group_id', $groupID)
                    //                 ->update(['status' => 'finished']);
                    //         }
    
                    //         Notification::make()
                    //             ->title('Approval processed successfully!')
                    //             ->success()
                    //             ->send();
                    //     })
                    //     // ->hidden(fn ($livewire) => ApprovalChain::where('mail_id', $livewire->record->id)
                    //     //     ->where('group_id', session('groupID'))
                    //     //     ->whereNot('status', 'waiting')
                    //     //     ->exists()
                    //     // )
                    //     ,
    
                    // // Decline Button with Note
                    // Actions\Action::make('declineWithNote')
                    //     ->label('Tolak')
                    //     ->icon('heroicon-o-x-circle')
                    //     ->color('danger')
                    //     ->requiresConfirmation()
                    //     ->form([
                    //         Textarea::make('notes')
                    //             ->label('Tambahkan Catatan')
                    //             ->required(),
                    //     ])
                    //     ->action(function ($livewire, array $data) {
                    //         $record = $livewire->record;
    
                    //         ApprovalChain::where('mail_id', $record->id)
                    //             ->where('group_id', session('groupID'))
                    //             ->update([
                    //                 'status' => 'denied',
                    //                 'notes' => $data['notes'],
                    //             ]);
    
                    //         // Change mail status back to "Draft"
                    //         $record->update([
                    //             'status' => 'Draft',
                    //             'notes' => $data['notes'],
                    //         ]);
    
                    //         Notification::make()
                    //             ->title('Mail declined with notes successfully! Status set to Draft.')
                    //             ->success()
                    //             ->send();
                    //     })
                    //     // ->hidden(fn ($livewire) => ApprovalChain::where('mail_id', $livewire->record->id)
                    //     //     ->where('group_id', session('groupID'))
                    //     //     ->whereNot('status', 'waiting')
                    //     //     ->exists()
                    //     // )
                    //     ,
                // ])

                
            ]) ;
        
    }

  
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject'),
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
                    ApprovalChain::where('mail_id', $record->id)
                        ->where('group_id', session('groupID'))
                        ->value('status') === 'waiting' ? 'Edit' : 'View'
                )
                ->icon(fn ($record) => 
                    ApprovalChain::where('mail_id', $record->id)
                        ->where('group_id', session('groupID'))
                        ->value('status') === 'waiting' ? 'heroicon-o-pencil' : 'heroicon-o-eye'
                )
                ->modalHeading(fn ($record) => 
                    ApprovalChain::where('mail_id', $record->id)
                        ->where('group_id', session('groupID'))
                        ->value('status') === 'waiting' ? 'Edit Record' : 'View Record'
                )
                ->form(fn ($record) => 
                    ApprovalChain::where('mail_id', $record->id)
                        ->where('group_id', session('groupID'))
                        ->value('status') === 'waiting' 
                        ? [/* Your editable form fields */] 
                        : [] // No form fields for "View" mode
                )
                ->color(fn ($record) => 
                    ApprovalChain::where('mail_id', $record->id)
                        ->where('group_id', session('groupID'))
                        ->value('status') === 'waiting' ? 'primary' : 'secondary'
                )
                ,
                // Action::make('approveMail')
                // ->label('Approve')
                // ->requiresConfirmation()
                // ->modalHeading('Approve Mail?')
                // ->modalSubheading('Are you sure you want to approve this mail? This action cannot be undone.')
                // ->modalButton('Yes, Approve')
                // ->url(function ($record) {
                //     // dd($record->id); // Dumps the record ID
                //     return ReceivedMailsResource::getUrl('approveMail', ['record' => $record->id]);
                // }),
            
                // ->requiresConfirmation()
                // ->action(fn ($record) => redirect(ReceivedMailsResource::getUrl('approveMail', ['record' => $record->id]))),
                Action::make('approveMail')
                ->label('Approve')
                ->icon('heroicon-o-envelope')
                ->requiresConfirmation()->action(fn ($record) => dd(Mail::find($record->id)->id))

                ->action(function ($record) {
                   
                //   dd($record->id);
                    $groupID = session('groupID'); // Get the current group ID
                    
                    $currentApproval = ApprovalChain::where('mail_id', $record->id)
                    ->where('group_id', $groupID)->orderBy('id') // Ensure it's in order
                    ->first();
                    // Update the approval status for the current group
            // dd($record->id);
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
            // Notify all users in the group
        $groupUsers = User::whereIn('id', function ($query) use ($record) {
            $query->select('user_id')
                ->from('group_details')
                ->where('group_id', $record->group_id);
        })->get();

        foreach ($groupUsers as $user) {
            $user->notify(new ApprovalProcessed('Approved', $record));
        }

                    Notification::make()
                        ->title('Approval processed successfully!')
                        ->success()
                        ->send();
                })
                ->color('success')
                ->extraAttributes(fn ($record) => [
                    'style' => ApprovalChain::where('mail_id', $record->id)
                        ->where('group_id', session('groupID'))
                        ->whereNot('status', 'waiting')
                        ->exists() ? 'display: none;' : ''
                ])
               
                ,
            
            Action::make('declineWithNote')
            ->label('Tolak') // Button label
            ->icon('heroicon-o-x-circle') // Optional: Add an icon
            ->form([
                Textarea::make('notes')
                    ->label('Tambahkan Catatan')
                    ->required(),
            ])
            // ->action(fn ($record) => dd($record->id))
            ->action(function ($record, array $data) {
                // Update the ApprovalChain with the user's notes and set status to 'denied'
                ApprovalChain::where('mail_id', $record->id)
                    ->where('group_id', session('groupID'))
                    ->update([
                        'status' => 'denied',
                        'notes' => $data['notes'],
                    ]);
                    // dd($approval);
                // Change mail status back to "Draft"
                $record->update(['status' => 'Draft']);
                $record->update(['notes' =>  $data['notes']]); 
                // Send a success notification
                Notification::make()
                    ->title('Mail declined with notes successfully! Status set to Draft.')
                    ->success()
                    ->send();
            })
            ->requiresConfirmation()

            ->color('danger')
            
            ->extraAttributes(fn ($record) => [
                'style' => ApprovalChain::where('mail_id', $record->id)
                    ->where('group_id', session('groupID'))
                    ->whereNot('status', 'waiting')
                    ->exists() ? 'display: none;' : ''
            ]),
            

            
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
            // 'approveMail' => Pages\ApproveMail::route('/{record}/approve'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $groupId = session('groupID');

        // Return empty query if session does not have a group ID
        if (!$groupId) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        $query = parent::getEloquentQuery()->withoutGlobalScopes();

        // Get approved mail IDs for the current group
        $approvedMailIds = ApprovalChain::where('group_id', $groupId)
            ->where('status', 'Approved')
            ->pluck('mail_id')
            ->toArray(); // Convert to an array for better performance

        $query->where(function ($q) use ($groupId, $approvedMailIds) {
            $q->where('target_id', $groupId)
            ->where('status', 'Submitted');

            if (!empty($approvedMailIds)) {
                $q->orWhereIn('id', $approvedMailIds);
            }
        });

        return $query;
    }

    

}
