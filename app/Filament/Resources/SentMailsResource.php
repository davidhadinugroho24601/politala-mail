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
use App\Models\MailUserView;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Panel;
use App\Http\Middleware\CheckGroupIDSession;

class SentMailsResource extends Resource
{
    protected static ?string $model = Mail::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
           
            // Final Target ID - Dropdown populated with user names
            Forms\Components\Select::make('final_target_id')
                ->label('Final Target')
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->required(),
                TextInput::make('subject'),
                RichEditor::make('content')
                ->label('Mail Content')
                ->columnSpan('full') // Ensures it uses the full width of the container
                ->extraAttributes(['style' => ' word-wrap: break-word;']) // Ensures proper text wrapping
                ->toolbarButtons([
                    'bold', 
                    'italic', 
                    'underline', 
                    'strike', 
                    'link', 
                    'bulletList', 
                    'orderedList', 
                    'blockquote', 
                    'codeBlock', 
                    'undo', 
                    'redo'
    ]),
    Forms\Components\Select::make('group_id')
    ->label('Group')
    ->options(
        Group::where('id', session('groupID'))->pluck('name', 'id') // Filter options by session groupID
    )
    ->searchable()
    ->required()
    ->disabled()
    ->default(session('groupID')),

            

        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
            Tables\Columns\TextColumn::make('writer_name')->label('Writer Name')->searchable(),
            Tables\Columns\TextColumn::make('target_name')->label('Target Name'),
            Tables\Columns\TextColumn::make('group_name')->label('Group Name'),
            Tables\Columns\TextColumn::make('status')->label('Approval Status'),
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
}
