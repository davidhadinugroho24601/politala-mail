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
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $title = 'Daftar Role';
    protected static ?string $navigationLabel = 'Daftar Role';
    protected static ?string $slug = 'your-roles';
    protected static string $view = 'filament.pages.enter-as';
    // protected static string $layout = 'layouts.filament-custom';


    public array $users = [];
    public array $groups = [];
    public array $peers = [];
    public array $groupIds = [];
    // protected static bool $shouldRegisterNavigation = false;
 
    public function mount(): void
    {
        $this->users = User::all()->toArray();
    
        $this->groupIds = GroupDetailsView::where('user_id', Auth::id())
            ->pluck('group_id')
            ->toArray();
    
        // Get peer IDs of current groups
        $peerIds = Group::whereIn('id', $this->groupIds)->pluck('peer_id')->filter()->toArray();
    
        // Merge peer IDs into group IDs, remove duplicates
        $this->groupIds = array_unique(array_merge($this->groupIds, $peerIds));
    
        // Load all groups including peers
        $this->groups = Group::whereIn('id', $this->groupIds)->get()->toArray();
    
        // Optional: load peer group models separately if needed
        $this->peers = Group::whereIn('id', $peerIds)->get()->toArray();
        // dd(Auth::user());

    }
    


       
}
