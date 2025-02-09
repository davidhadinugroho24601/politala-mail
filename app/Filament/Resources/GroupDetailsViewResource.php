<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupDetailsViewResource\Pages;
use App\Filament\Resources\GroupDetailsViewResource\RelationManagers;
use App\Models\GroupDetailsView;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Models\User;
use App\Models\Group;
use Filament\Tables\Columns\SelectColumn;
use App\Http\Middleware\CheckGroupIDSession;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupDetailsViewResource extends AdminResource
{
    protected static ?string $model = GroupDetailsView::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getLabel(): string
    {
        return 'Users Position'; // This will change the resource's label everywhere
    }
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('User')
                    ->relationship('users', 'name') 
                    ->searchable(), 
                Select::make('group_id')
                    ->label('Group')
                    ->relationship('groups', 'name') 
                    ->searchable(), 
            ]);
    }

    public static function table(Table $table): Table
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
    SelectColumn::make('group_id')
    ->label('Group')
    ->options(
        Group::query()->pluck('name', 'id')->toArray() // Replace 'name' and 'id' as needed
    )->searchable(query: function (Builder $query, string $search): Builder {
        return $query
            ->where('group_name', 'like', "%{$search}%")
            ;
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroupDetailsViews::route('/'),
            'create' => Pages\CreateGroupDetailsView::route('/create'),
            'edit' => Pages\EditGroupDetailsView::route('/{record}/edit'),
        ];
    }
  
    public static function getRouteMiddleware(Panel $panel): array
    {
        // Apply the middleware to the UserResource routes
        return [
            'checkGroupID' => CheckGroupIDSession::class,
        ];
    }




}
