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
use App\Models\MailPath;
use App\Services\PathService;
use App\Filament\Resources\MailPathResource;
// use Filament\Resources\Pages\CreateRecord;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class MailPathRelationManager extends RelationManager 
{
    protected static string $relationship = 'MailPath';
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->type === 'staged';
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
                ->mutateFormDataUsing(function (array $data, PathService $pathService): array {
                   
                    $pathService->findShortestPath($data['sender_id'],$data['receiver_id']);
                    
                    // Ubah data sebelum disimpan jika diperlukan
                    return $data;
                })
                ->after(function (MailPath $record, PathService $pathService) { // Pastikan tipe modelnya sesuai
                    // PathDetail::create([
                    //     'path_id' => $record->id, // Gunakan ID dari record yang baru dibuat
                    //     'group_id' => $record->sender_id, // Gunakan ID dari record yang baru dibuat
                    //     'order' => 1,
                    //     // 'step' => 1, // Contoh nilai default
                    // ]);
                    // dd($record);

                    $pathDetail = [
                        'sender_id' => $record->sender_id,
                        'receiver_id' => $record->receiver_id,
                        'template_id' => $record->template_id,
                        'path_id' => $record->id,
                    ];
                    $pathService->createPathDetail($pathDetail);
                    return $record;
                }),
            ])
            ->actions([
                Action::make('edit_mail_path')
                ->label('Edit Mail Path')
                ->icon('heroicon-o-pencil')
                ->url(fn ($record) => MailPathResource::getUrl('edit', ['record' => $record->id])),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
