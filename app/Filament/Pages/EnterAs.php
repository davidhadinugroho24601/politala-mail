<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupDetailsView;
use Filament\Widgets\Card;
use Filament\Forms\Components\Card as FormCard;
use Illuminate\Support\Facades\Auth;

class EnterAs extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $title = 'Daftar Role';
    protected static ?string $navigationLabel = 'Daftar Role';
    protected static ?string $slug = 'your-roles';
    protected static string $view = 'filament.pages.enter-as';
    // protected static string $layout = 'layouts.filament-custom';


    public array $users = [];
    public array $groups = [];
    public array $groupIds = [];
    // protected static bool $shouldRegisterNavigation = false;

    public function mount(): void
    {
        $this->users = User::all()->toArray(); // Fetch all users and convert to array
        $this->groupIds = GroupDetailsView::where('user_id', Auth::id())
            ->pluck('group_id') // Pluck just the group_id values
            ->toArray(); // Convert to array

            $this->groups = Group::whereIn('id', $this->groupIds)->get()->toArray(); // Convert collection to array
        }

       
}
