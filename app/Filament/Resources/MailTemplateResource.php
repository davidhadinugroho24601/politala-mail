<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MailTemplateResource\Pages;
use App\Filament\Resources\MailTemplateResource\RelationManagers;
use App\Models\MailTemplate;
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

class MailTemplateResource extends AdminResource
{
    protected static ?string $model = MailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),

                RichEditor::make('template')
                ->label('Mail Content')
                ->columnSpan('full')
                ->disabled(fn ($record) => $record && $record->status !== 'Draft')

                ->extraAttributes(['style' => 'word-wrap: break-word;'])
                ->afterStateUpdated(function ($state, $record) {
                    if (!$record) {
                        return;
                    }
            
                    // Extract image URLs and store them in the media library
                    preg_match_all('/<img[^>]+src="([^">]+)"/', $state, $matches);
            
                    foreach ($matches[1] as $url) {
                        // Store images in the media library
                        $record->addMediaFromUrl($url)
                            ->toMediaCollection('attachments');
                    }
            
                    // Keep only <img> tags and remove everything else inside <figure>
                    $cleanContent = preg_replace('/<figure[^>]*>.*?(<img[^>]+>).*?<\/figure>/s', '$1', $state);
            
                    // Remove any remaining <figcaption> just in case
                    $cleanContent = preg_replace('/<figcaption[^>]*>.*?<\/figcaption>/s', '', $cleanContent);
            
                    // Update the content without captions
                    $record->update(['content' => $cleanContent]);
                }),
            

            
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
            //
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
