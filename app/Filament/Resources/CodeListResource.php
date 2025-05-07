<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CodeListResource\Pages;
use App\Filament\Resources\CodeListResource\RelationManagers;
use App\Models\CodeList;
use App\Models\Mail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Carbon\Carbon;
class CodeListResource extends AdminResource
{
    protected static ?string $model = CodeList::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            
                Select::make('mail_id')
                ->label('Mail')
                ->options(
                    Mail::with('writer')->get()->mapWithKeys(function ($mail) {
                        Carbon::setLocale('id');
                        $formattedDate = Carbon::parse($mail->updated_at)->translatedFormat('d F Y H:i');
                
                        return [
                            $mail->id => $mail->subject . ' - ' . ($mail->writer->name ?? 'Pengirim Tidak Diketahui') . ' (' . ($mail->group->name ?? 'Jabatan Pengirim Tidak Diketahui') . ') ' . ' - ' . $formattedDate,
                        ];
                    })
                )
                ->getOptionLabelUsing(fn ($value) => Mail::find($value)?->subject)
                ->searchable()
                ->required()
                ->rules('unique:code_lists,mail_id', 'The selected mail has already been assigned.') // Ensure mail_id is unique
                ->validationAttribute('Mail ID'),
            
            TextInput::make('code')
                ->label('Code')
                ->required()
                ->maxLength(255),
            
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                ->label('Code')
                ->sortable()
                ->searchable(),
    
                TextColumn::make('mail.subject') // assuming 'subject' exists in Mail
                ->label('Mail Subject')
                ->sortable()
                ->searchable(),
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
            'index' => Pages\ListCodeLists::route('/'),
            'create' => Pages\CreateCodeList::route('/create'),
            'edit' => Pages\EditCodeList::route('/{record}/edit'),
        ];
    }
}
