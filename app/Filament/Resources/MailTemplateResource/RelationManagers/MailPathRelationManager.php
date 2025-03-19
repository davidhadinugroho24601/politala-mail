<?php

namespace App\Filament\Resources\MailTemplateResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table; 
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use App\Models\PathDetail;

class MailPathRelationManager extends RelationManager
{
    protected static string $relationship = 'MailPath';
    protected function mutateFormDataBeforeSave(array $data): array
    {
        dd($data);
                // Insert a new PathDetail record
        PathDetail::create([
        'path_id' => $data['id'], // Adjust fields accordingly
        // 'step' => 1, // Example value
        ]);
        // $data['created_by'] = auth()->id(); // Set the creator of the record
        return $data;
    }
    public function form(Form $form): Form
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sender.name')
            ->columns([
                Tables\Columns\TextColumn::make('sender.name'),
                Tables\Columns\TextColumn::make('receiver.name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    // Ubah data sebelum disimpan jika diperlukan
                    return $data;
                })
                ->after(function (MailPath $record) { // Pastikan tipe modelnya sesuai
                    PathDetail::create([
                        'path_id' => $record->id, // Gunakan ID dari record yang baru dibuat
                        // 'step' => 1, // Contoh nilai default
                    ]);
                    return $record;
                }),
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
