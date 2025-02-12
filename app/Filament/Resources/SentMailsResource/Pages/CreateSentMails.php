<?php

namespace App\Filament\Resources\SentMailsResource\Pages;

use App\Filament\Resources\SentMailsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\ApprovalChain;
use App\Models\Group;
use App\Models\MailTemplate;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;

class CreateSentMails extends CreateRecord
{
    protected static string $resource = SentMailsResource::class;

    /**
     * Find the shortest path between two group IDs.
     */
    private function findShortestPath($startId, $finalId) {
        // Initialize the queue with the starting node and its path
        $queue = [[$startId]];
        // Keep track of visited nodes to prevent revisiting
        $visited = [$startId => true];
    
        while (!empty($queue)) {
            // Dequeue the first path from the queue
            $path = array_shift($queue);
            // Get the last node in the current path
            $currentId = end($path);
    
            // If the current node is the final destination
            if ($currentId == $finalId) {
                // Remove the start node from the result if there are multiple nodes
                return count($path) > 1 ? array_slice($path, 1) : $path;
            }
    
            // Retrieve child groups of the current node
            $children = Group::where('parent_id', $currentId)->pluck('id')->toArray();
            // Retrieve parent group of the current node
            $parent = Group::where('id', $currentId)->value('parent_id');
    
            // Merge both child and parent nodes into one array
            $neighbors = array_filter(array_merge($children, [$parent]));
    
            // Iterate over each neighbor (child and parent)
            foreach ($neighbors as $neighborId) {
                if (!isset($visited[$neighborId])) {
                    $visited[$neighborId] = true;
                    $newPath = $path;
                    $newPath[] = $neighborId;
                    $queue[] = $newPath;
                }
            }
        }
    
        // If the finalId is not reachable from the startId, return an empty array
        return [];
    }
    

    /**
     * Get all parent groups of a given group ID.
     */
    // private function getParentGroupIds($groupId, $finalId)
    // {
    //     $parentIds = [];
    //     $group = Group::find($groupId);

    //     while ($group && $group->parent_id) {
    //         $parentIds[] = $group->parent_id;
    //         if ($group->parent_id == $finalId) break;
    //         $group = Group::find($group->parent_id);
    //     }

    //     return $parentIds;
    // }

    /**
     * Handle record creation with approval chain logic.
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $data['writer_id'] = auth()->id();
        $data['status'] = 'Draft';
        $data['content'] = MailTemplate::find($data['template_id'])?->template ?? '';

        $record = $this->getModel()::create($data);
       
        
        $currentGroupId = $record->group_id;
        // $status = 'down';

        $pathIDs = $this->findShortestPath($currentGroupId, $data['final_id']);
        // $childIDs = $this->findShortestPath($currentGroupId, $data['final_id']);

        // if (in_array($record->final_id, $parentIDs)) {
        //     $status = 'up';
        // }

        if ($record->is_staged === 'yes') {
            $this->createApprovalChain($record->id, $pathIDs);
            if (!empty($pathIDs)) {
                // Update the record with the first target_id in the path
                $record->update(['target_id' => $pathIDs[0]]);
            }
        }
        // dd($pathIDs);
        $this->validateTargetConnection($currentGroupId, $data['final_id']);

        return $record;
    }

    /**
     * Create approval chain entries.
     */
    private function createApprovalChain($mailId, array $groupIds)
    {
        $approvalChains = array_map(fn ($groupId) => [
            'mail_id' => $mailId,
            'group_id' => $groupId,
        ], $groupIds);
        if (!empty($approvalChains)) {
            ApprovalChain::insert($approvalChains);
        }
    }

    /**
     * Validate if the final_id is connected to the sender.
     */
    private function validateTargetConnection($currentGroupId, $finalId)
    {
        $connectedGroups = array_merge(
            $this->findShortestPath($currentGroupId, $finalId),
            $this->findShortestPath($finalId, $currentGroupId)
        );

        if (!in_array($finalId, $connectedGroups) && $finalId != session('groupID')) {
            Notification::make()
                ->title('Invalid Target')
                ->body('The selected target is not connected to the sender.')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'final_id' => 'Invalid operation: The selected group is not connected.',
            ]);
        }
    }
}
