<?php

namespace App\Filament\Resources\ReceivedMailsResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use ZipArchive;

class AttachmentMailRelationManager extends RelationManager
{
    protected static string $relationship = 'AttachmentMail';

    public function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\TextInput::make('description')
                ->required()
                ->maxLength(255),

        
        FileUpload::make('path')->directory('attachments')->multiple()->columnSpan('full')->enableDownload(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
        ->recordTitleAttribute('description')
        ->columns([
            Tables\Columns\TextColumn::make('description'),
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
