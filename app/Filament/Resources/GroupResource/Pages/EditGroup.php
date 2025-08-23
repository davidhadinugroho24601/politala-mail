<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Group;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;

class EditGroup extends EditRecord
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    function getChildGroupIds($groupId, array &$visited = []): array
    {
        // If we've already processed this group, return an empty array.
        if (in_array($groupId, $visited)) {
            return [];
        }
        
        // Mark the current group as visited.
        $visited[] = $groupId;
        
        $childIds = [];
        // Get direct child groups.
        $children = Group::where('parent_id', $groupId)->get();
        
        foreach ($children as $child) {
            // Avoid re-processing a child if already visited.
            if (in_array($child->id, $visited)) {
                continue;
            }
            
            $childIds[] = $child->id;
            // Recursively get children of the current child.
            $childIds = array_merge($childIds, $this->getChildGroupIds($child->id, $visited));
        }
        
        return $childIds;
    }
    
    function getParentGroupIds($groupId, array &$visited = []): array
    {
        $parentIds = [];
        $group = Group::find($groupId);
        
        // Continue traversing up while there's a parent and it hasn't been processed yet.
        while ($group && $group->parent_id && !in_array($group->parent_id, $visited)) {
            $visited[] = $group->parent_id;
            $parentIds[] = $group->parent_id;
            $group = Group::find($group->parent_id);
        }
        
        return $parentIds;
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
{
    $currentGroupId = $this->record->id;
    
    if (isset($data['parent_id'])) {
        // Retrieve descendant and ancestor IDs using our updated functions.
        $childIds = $this->getChildGroupIds($currentGroupId);
        $parentIds = $this->getParentGroupIds($currentGroupId);
        
        if (in_array($data['parent_id'], $childIds)) {
            Notification::make()
                ->title('Invalid Parent Group')
                ->body('The selected parent group is a descendant of the current group.')
                ->danger()
                ->send();
    
            throw ValidationException::withMessages([
                'parent_id' => 'Invalid operation: The selected parent group is a descendant of the current group.',
            ]);
        }
        
        if (in_array($data['parent_id'], $parentIds)) {
            Notification::make()
                ->title('Invalid Parent Group')
                ->body('The selected parent group is an ancestor of the current group.')
                ->danger()
                ->send();
    
            throw ValidationException::withMessages([
                'parent_id' => 'Invalid operation: The selected parent group is an ancestor of the current group.',
            ]);
        }
    }
    
    return $data;
}
    
    
    
}
