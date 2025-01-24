<?php

namespace App\Filament\Resources\SentMailsResource\Pages;

use App\Filament\Resources\SentMailsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\ApprovalChain;
use App\Models\Group;


class CreateSentMails extends CreateRecord
{
    protected static string $resource = SentMailsResource::class;


    function getChildGroupIds($groupId) {
        $childIds = [];

        // Get direct child groups
        $children = Group::where('parent_id', $groupId)->get();

        foreach ($children as $child) {
            $childIds[] = $child->id;

            // Recursively get children of the current child
            $childIds = array_merge($childIds, $this->getChildGroupIds($child->id));
        }

        return $childIds;
    }

    function getParentGroupIds($groupId) {
        $parentIds = [];
    
        $group = Group::find($groupId);
    
        while ($group && $group->parent_id) {
            $parentIds[] = $group->parent_id;
            $group = Group::find($group->parent_id);
        }
    
        return $parentIds;
    }
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        
        // Fetch groups where parent_id is not null
        // Set the writer_id to the authenticated user's ID
        $data['writer_id'] = auth()->id();
        $data['status'] = 'Draft';

        // Save the record first to get the ID
        $record = $this->getModel()::create($data);
        // dd($record->group_id);
        $currentGroupId = $record->group_id;
        $status='down';
        $parentIDs = $this->getParentGroupIds($currentGroupId);
        $childIDs = $this->getChildGroupIds($currentGroupId);

        if ($record->is_staged === 'yes') {
            // Assuming $status is defined elsewhere in your code (either "down" or "up")
        if ($status == 'down') {
            // Include the current group in the chain first
            $chain = [
                'mail_id' => $record->id, // Use the saved record's ID
                'group_id' => $currentGroupId, // Include the current group ID
            ];
            ApprovalChain::insert($chain);
        
            // Run child group logic
            foreach ($childIDs as $childId) {
                $chain = [
                    'mail_id' => $record->id, // Use the saved record's ID
                    'group_id' => $childId,   // Use the child_id for the group
                ];
        
                // dd($chain); // You can still use dd() for debugging
                ApprovalChain::insert($chain);
            }
        }
        
        elseif ($status == 'up') {

            $chain = [
                'mail_id' => $record->id, // Use the saved record's ID
                'group_id' => $currentGroupId, // Include the current group ID
            ];
            ApprovalChain::insert($chain);

            // Run parent group logic
            foreach ($parentIDs as $parentId) {
                $chain = [
                    'mail_id' => $record->id, // Use the saved record's ID
                    'group_id' => $parentId,   // Use the parent_id for the group
                ];

                // dd($chain); // You can still use dd() for debugging
                ApprovalChain::insert($chain);
            }
        }
        }
        
        
        

        return $record;
    }

}
