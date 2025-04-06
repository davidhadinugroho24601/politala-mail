<?php

namespace App\Filament\Resources\MailPathResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PathDetailRelationManager extends RelationManager
{
    protected static string $relationship = 'PathDetail';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('group.name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('group.name')
            ->columns([
                Tables\Columns\TextColumn::make('group.name')
                ->label('Jabatan')
                , 
                Tables\Columns\SelectColumn::make('authority')
                ->options([
                    'skip' => 'Lewati',
                    'read' => 'Mengetahui',
                    'approve' => 'Menyetujui',
                ])
                ->disablePlaceholderSelection()
                ->label('Izin')
                , 
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
