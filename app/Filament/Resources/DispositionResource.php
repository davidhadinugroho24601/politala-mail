<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DispositionResource\Pages;
use App\Filament\Resources\DispositionResource\RelationManagers;
use App\Models\Disposition;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DispositionResource extends AdminResource
{
    protected static ?string $model = Disposition::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Disposisi';

    protected static ?string $modelLabel = 'Disposisi';

    protected static ?string $pluralModelLabel = 'Disposisi';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label('Label disposisi'),
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
            'index' => Pages\ListDispositions::route('/'),
            'create' => Pages\CreateDisposition::route('/create'),
            'edit' => Pages\EditDisposition::route('/{record}/edit'),
        ];
    }
}
