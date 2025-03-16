<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MailCodesResource\Pages;
use App\Filament\Resources\MailCodesResource\RelationManagers;
use App\Models\MailCode;
use App\Models\MailCodeDetail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\NumberInput;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\MailCodesResource\RelationManagers\CodeDetailsRelationManager;

class MailCodesResource extends AdminResource
{
    protected static ?string $model = MailCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Kode surat';

    protected static ?string $modelLabel = 'Kode surat';

    protected static ?string $pluralModelLabel = 'Kode surat';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('code_name')->label('Label Kode'), 
                
                Select::make('status')
                ->label('Status Kode')
                ->options(
                    [
                        'enabled' => 'Aktif',
                        'disabled' => 'nonaktif',
                    ]
                )
                ->required(),
                
                TextInput::make('code')
                ->label('Kode Surat')->disabled(),
                // ->afterStateHydrated(function ($component, $state, $record) {
                //     if ($record) {
                //         $mailCodeId = $record->id;
                //         $mergedText = MailCodeDetail::where('code_id', $mailCodeId)
                //             ->pluck('text')
                //             ->map(fn($text) => trim(preg_replace('/\s+/', ' ', $text)))
                //             ->implode('/');
                //         $component->state($mergedText);
                //     }
                // })
                // ->beforeStateDehydrated(function ($state, $record, $set) {
                //     if ($record) {
                //         $record->code = $state;
                //     } else {
                //         $set('code', $state);
                //     }
                // })
                // ->disabled(),
            
            
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('code_name')
            ->label('Label Kode')
            ->sortable()
            ->searchable(),
            TextColumn::make('status')
            ->label('Status')
            ->searchable()    
            ->formatStateUsing(fn($state) => match ($state) {
                'enabled' => 'Aktif',
                'disabled' => 'nonaktif',
                default => 'Unknown',
            }),
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
            CodeDetailsRelationManager::class, 
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMailCodes::route('/'),
            'create' => Pages\CreateMailCodes::route('/create'),
            'edit' => Pages\EditMailCodes::route('/{record}/edit'),
        ];
    }
}
