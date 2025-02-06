<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserGroupsResource\Pages;
use App\Filament\Resources\UserGroupsResource\RelationManagers;
use App\Models\Group;
use App\Models\User;
use App\Models\GroupDetailsView;
use Illuminate\Support\Facades\Auth;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\SelectColumn;
use Filament\Panel;
use App\Http\Middleware\CheckGroupIDSession;

class UserGroupsResource extends AdminResource
{
    protected static ?string $model = Group::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $shouldRegisterNavigation = false;

    public static function getEloquentQuery(): Builder
    {
        $groupIds = GroupDetailsView::where('user_id', Auth::id())
        ->pluck('group_id')
        ->toArray();

        // Now use $groupIds in the query to filter the results
        return parent::getEloquentQuery()->whereIn('id', $groupIds);

    }


    public static function getLabel(): string
    {
        return 'Your Groups'; // This will change the resource's label everywhere
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('name')
                ->translateLabel()
                ->sortable()
                ->searchable()->searchable(query: function (Builder $query, string $search): Builder {
                    return $query
                        ->where('name', 'like', "%{$search}%")
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
            'index' => Pages\ListUserGroups::route('/'),
            'create' => Pages\CreateUserGroups::route('/create'),
            'edit' => Pages\EditUserGroups::route('/{record}/edit'),
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
