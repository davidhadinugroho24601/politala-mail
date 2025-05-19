<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MailTemplateResource\Pages;
use App\Filament\Resources\MailTemplateResource\RelationManagers;
use App\Models\MailTemplate;
use App\Models\Division;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\View;
use Filament\Forms\Components\Select;
// use App\Filament\Resources\MailTemplateResource\RelationManagers\TemplateAvailabilityRelationManager;
use App\Filament\Resources\MailTemplateResource\RelationManagers\MailPathRelationManager;
class MailTemplateResource extends AdminResource
{
    protected static ?string $model = MailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Template';

    protected static ?string $modelLabel = 'Template';

    protected static ?string $pluralModelLabel = 'Template';

   
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),

                View::make('components.template-google-docs-editor')
                ->label('Google Docs Editor')
                ->columnSpan('full')
                ->disabled()
                ->hidden(fn (string $context): bool => $context !== 'edit')
                ->extraAttributes(['style' => 'width: 100%; height: 600px; border: none;']),
                
                Select::make('type')
                ->required()
                ->live() // Makes it update the form instantly
                ->label('Tipe Surat')
                    ->options([
                        'staged' => 'Berjenjang',
                        'direct' => 'Langsung',
                    ])                
                    ->disabled(fn ($record) => $record !== null),
                
              

            
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
               
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
            MailPathRelationManager::class,
        ];
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMailTemplates::route('/'),
            'create' => Pages\CreateMailTemplate::route('/create'),
            'edit' => Pages\EditMailTemplate::route('/{record}/edit'),
        ];
    }
}
