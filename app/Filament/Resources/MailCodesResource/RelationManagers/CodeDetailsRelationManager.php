<?php

namespace App\Filament\Resources\MailCodesResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class CodeDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'codeDetails';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('text')
                    ->required()
                    ->maxLength(255),
                
                Select::make('type')
                ->options([
                    'text' => 'Text',
                    'increment' => 'Increment',
                    'date' => 'Tanggal',
                    'month' => 'Bulan',
                    'year' => 'Tahun',
                ])->selectablePlaceholder(false),
                
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('text')
            ->columns([
                Tables\Columns\TextColumn::make('text'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
