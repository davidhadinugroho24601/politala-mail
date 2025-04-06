<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MailPathResource\Pages;
use App\Filament\Resources\MailPathResource\RelationManagers;
use App\Models\MailPath;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MailPathResource\RelationManagers\PathDetailRelationManager;
use Filament\Forms\Components\Select;

class MailPathResource extends Resource
{
    protected static ?string $model = MailPath::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $shouldRegisterNavigation = true;
    
   
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('sender_id')
                ->relationship('sender', 'name')
                ->required()
                ->label('Pengirim'),
            
                Select::make('receiver_id')
                    ->relationship('receiver', 'name')
                    ->required()
                    ->label('Penerima'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tables\Columns\TextColumn::make('sender.name'),
                // Tables\Columns\TextColumn::make('receiver.name'),
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
            PathDetailRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMailPaths::route('/'),
            'create' => Pages\CreateMailPath::route('/create'),
            'edit' => Pages\EditMailPath::route('/{record}/edit'),
        ];
    }
}
