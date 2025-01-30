<?php

namespace App\Filament\Resources\GroupResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\SelectColumn;
use App\Models\User;
use App\Models\Group;
use Filament\Forms\Components\Select;

class GroupDetailsViewRelationManager extends RelationManager
{
    protected static string $relationship = 'GroupDetailsView';

    public static string $labelFirstWord = 'Group ';
    public static string $labelSecondWord = 'Member';

    // Method to get labels dynamically
    public static function getLabel(string $type): string
    {
        if ($type === "Singular") {
            return self::$labelFirstWord . self::$labelSecondWord;
        } else {
            return self::$labelFirstWord . self::$labelSecondWord . 's';
        }
    }

    // Override the title method
    public static function getTitle($ownerRecord, $pageClass): string
    {
        return self::getLabel("Plural"); // Use the static method
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('User')
                    ->relationship('users', 'name') 
                    ->searchable(), 
           
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                SelectColumn::make('user_id')
    ->label('User')
    ->options(
        User::query()->pluck('name', 'id')->toArray() // Replace 'name' and 'id' as needed
    )->searchable(query: function (Builder $query, string $search): Builder {
        return $query
            ->where('user_name', 'like', "%{$search}%")
            ;
    }), 
   
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make() ->label('New '. self::getLabel("Singular")),
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
