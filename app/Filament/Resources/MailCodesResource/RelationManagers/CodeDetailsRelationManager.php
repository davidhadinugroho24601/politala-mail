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
                Select::make('type')
                ->options([
                    'text' => 'Text',
                    'increment' => 'Surat Terbit',
                    'division' => 'Divisi',
                    'date' => 'Tanggal',
                    'month' => 'Bulan',
                    'year' => 'Tahun',
                ])
                ->reactive() // Make the select field reactive
                ->required(),
    
            TextInput::make('text')
                ->required()
                ->maxLength(255)
                ->visible(fn ($get) => $get('type') === 'text') // Show only when 'text' is selected
                ->placeholder('Enter text here'),
    
            
    

                
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('text')
            ->columns([
                Tables\Columns\TextColumn::make('text')->placeholder('Data belum tersedia'),
                Tables\Columns\TextColumn::make('type'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make() ->mutateFormDataUsing(function (array $data): array {
                    // Modify the form data before saving
                    switch ($data['type']) {
                        case 'increment':
                            $data['text'] = '{surat terbit}';
                            break;

                        case 'date':
                            $data['text'] = '{tanggal}';
                            break;
                            
                        case 'month':
                            $data['text'] = '{bulan}';
                            break;
                            
                        case 'year':
                            $data['text'] = '{tahun}';
                            break;

                        case 'division':
                                $data['text'] = '{akronim divisi}';
                                break;
    
                        default:
                            // Optional: handle cases where 'type' doesn't match any case
                            break;
                    }
                    
                    return $data;
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
