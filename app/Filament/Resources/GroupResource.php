<?php
namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Filament\Resources\GroupResource\RelationManagers\GroupDetailsViewRelationManager;
use App\Models\Group;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\SelectColumn;
use App\Models\User;
use Filament\Panel;
use App\Http\Middleware\CheckGroupIDSession;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
                Select::make('parent_id')
                    ->label('Parent Group')
                    ->options(Group::all()->pluck('name', 'id')),
                   
             Select::make('manager_id')
              ->label('Manager')
              ->relationship('managers', 'name') 
              ->searchable(), 
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->translateLabel()
                    ->sortable()
                    ->searchable(),
                SelectColumn::make('parent_id')
                    ->label('Parent Group')
                    ->options(Group::all()->pluck('name', 'id')),

                        SelectColumn::make('manager_id')
            ->label('Manager')
            ->options(
                User::query()->pluck('name', 'id')->toArray() // Replace 'name' and 'id' as needed
            )->searchable(query: function (Builder $query, string $search): Builder {
                return $query
                    ->where('user_name', 'like', "%{$search}%")
                    ;
            }), 
            ])
            ->filters([
                // Add any filters here
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            GroupDetailsViewRelationManager::class, 
        ];
    }
    public static function getRouteMiddleware(Panel $panel): array
    {
        // Apply the middleware to the UserResource routes
        return [
            'checkGroupID' => CheckGroupIDSession::class,
        ];
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }

    
}
