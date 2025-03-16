<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignmentLetterResource\Pages;
use App\Filament\Resources\AssignmentLetterResource\RelationManagers;
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
use Filament\Forms\Components\View;
use App\Services\MailService;

class AssignmentLetterResource extends Resource
{
    protected static ?string $model = Mail::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Surat tugas';

    protected static ?string $modelLabel = 'Surat tugas';

    protected static ?string $pluralModelLabel = 'Surat tugas';

    protected static bool $shouldRegisterNavigation = false;

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
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListAssignmentLetters::route('/'),
            'create' => Pages\CreateAssignmentLetter::route('/create'),
            'edit' => Pages\EditAssignmentLetter::route('/{record}/edit'),
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
    })
    ->whereHas('template', function ($query) {
        $query->where('name', 'Surat Tugas');
    });

    return $query;
}

}
