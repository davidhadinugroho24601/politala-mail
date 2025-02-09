<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReceivedMailsResource\Pages;
use App\Filament\Resources\ReceivedMailsResource\RelationManagers;
use App\Models\Mail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;

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
        return parent::getEloquentQuery()
    ->withoutGlobalScopes()
    ->where('target_id', session('groupID'))
    ->where('status', 'Submitted');

    }

}
