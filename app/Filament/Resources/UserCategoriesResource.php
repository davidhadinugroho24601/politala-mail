<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserCategoriesResource\Pages;
use App\Filament\Resources\UserCategoriesResource\RelationManagers;
use App\Models\UserCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\UserCategoriesResource\RelationManagers\UsersRelationManager;

class UserCategoriesResource extends AdminResource
{
    protected static ?string $model = UserCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationLabel = 'Golongan';

    protected static ?string $modelLabel = 'Golongan';

    protected static ?string $pluralModelLabel = 'Golongan';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
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
            UsersRelationManager::class, 
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserCategories::route('/'),
            'create' => Pages\CreateUserCategories::route('/create'),
            'edit' => Pages\EditUserCategories::route('/{record}/edit'),
        ];
    }
}
