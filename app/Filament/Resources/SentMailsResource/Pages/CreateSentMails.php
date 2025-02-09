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


    function findShortestPath($startId, $finalId) {
        // Initialize the queue with the starting node and the path leading to it
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
                // If the path length is greater than 1, remove the first node
                if (count($path) > 1) {
                    array_shift($path);
                }
                return $path;
            }
    
            // Retrieve child groups of the current node
            $children = Group::where('parent_id', $currentId)->pluck('id');
    
            // Iterate over each child
            foreach ($children as $childId) {
                // If the child hasn't been visited yet
                if (!isset($visited[$childId])) {
                    // Mark it as visited
                    $visited[$childId] = true;
                    // Enqueue a new path extending the current path with the child node
                    $newPath = $path;
                    $newPath[] = $childId;
                    $queue[] = $newPath;
                }
            }
        }
    
        // If the finalId is not reachable from the startId, return an empty array
        return [];
    }
    

    function getParentGroupIds($groupId, $finalId) {
        $parentIds = [];
    
        $group = Group::find($groupId);
    
        while ($group && $group->parent_id) {

            $parentIds[] = $group->parent_id;
            $group = Group::find($group->parent_id, $finalId);

            if ($child->id == $finalId) {break;}

        }
    
        return $parentIds;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
     
        // Fetch groups where parent_id is not null
        // Set the writer_id to the authenticated user's ID
        $data['writer_id'] = auth()->id();
        $data['status'] = 'Draft';
    // Check if a template_id was provided from the form submission
    if (isset($data['template_id'])) {
        // Retrieve the mail template based on the provided template_id
        $mailTemplate = MailTemplate::find($data['template_id']);
        // Assign the template content to the 'content' field if available
        $data['content'] = $mailTemplate ? $mailTemplate->template : '';
    } else {
        // Fallback if no template_id was provided
        $data['content'] = '';
    }
        // Save the record first to get the ID
        $record = $this->getModel()::create($data);
        // dd($record->group_id);
        $currentGroupId = $record->group_id;
        
        $status='down';

        $parentIDs = $this->findShortestPath($currentGroupId, $data['final_id']);
        $childIDs = $this->findShortestPath($currentGroupId, $data['final_id']);
        if (in_array($record->final_id, $parentIDs)) {
           $status = 'up';
        } 

        if ($record->is_staged === 'yes') {
            // Assuming $status is defined elsewhere in your code (either "down" or "up")
        if ($status == 'down') {
            

        
            // Run child group logic
            foreach ($childIDs as $childId) {
                $chain = [
                    'mail_id' => $record->id, // Use the saved record's ID
                    'group_id' => $childId,   // Use the child_id for the group
                ];
                // if (empty($childIDs)) {

                //     $chain = [
                //         'mail_id' => $record->id, // Use the saved record's ID
                //         'group_id' => session('groupID'),   // Use the child_id for the group
                //     ];
                // ApprovalChain::insert($chain);

                // }
                // dd($chain); // You can still use dd() for debugging
                ApprovalChain::insert($chain);
               

            }
            // dd($data['target_id']);

                            // Set 'target_id' to the first element of $parentIDs if it exists
                            if (!empty($childIDs)) {
                                $data['target_id'] = $parentIDs[0];
                                // dd($data['target_id']);

                            } else {
                                // Handle the case where $parentIDs is empty
                                // For example, set 'target_id' to null or handle accordingly
                                // dd($data['target_id']);
                                $data['target_id'] = null;
                            }
                
        }
        
        elseif ($status == 'up') {



            // Run parent group logic
            foreach ($parentIDs as $parentId) {
                $chain = [
                    'mail_id' => $record->id, // Use the saved record's ID
                    'group_id' => $parentId,   // Use the parent_id for the group
                ];

                // dd($chain); // You can still use dd() for debugging
                ApprovalChain::insert($chain);
              

            }
                        // Set 'target_id' to the first element of $parentIDs if it exists
            if (!empty($parentIDs)) {
                $data['target_id'] = $parentIDs[0];
            } else {
                // Handle the case where $parentIDs is empty
                // For example, set 'target_id' to null or handle accordingly
                $data['target_id'] = null;
            }

        }
        }
        
        
        $currentGroupId = $data['group_id'];
        if (isset($data['final_id'])) {
            // Retrieve descendant and ancestor IDs using our updated functions.
            $childIds = $this->findShortestPath($currentGroupId, $data['final_id']);
            $parentIds = $this->findShortestPath($currentGroupId, $data['final_id']);
            // dd($childIds);
            if (!in_array($data['final_id'], $childIds) and !in_array($data['final_id'], $parentIds) and $data['final_id']!= session('groupID')) {
                Notification::make()
                    ->title('Invalid Target')
                    ->body('The selected target is not connected to the sender.')
                    ->danger()
                    ->send();
        
                throw ValidationException::withMessages([
                    'final_id' => 'Invalid operation: The selected parent group is a descendant of the current group.',
                ]);
            }
            
            // if (!in_array($data['final_id'], $parentIds)) {
            //     Notification::make()
            //         ->title('Invalid Target')
            //         ->body('The selected target is not connected to the sender.')
            //         ->danger()
            //         ->send();
        
            //     throw ValidationException::withMessages([
            //         'parent_id' => 'Invalid operation: The selected parent group is an ancestor of the current group.',
            //     ]);
            // }
        }

        return $record;
    }

    // protected function afterCreate(): void
    // {
    //     $currentGroupId = $this->record->final_id;
    
    //     if (isset($data['final_id'])) {
    //         // Retrieve descendant and ancestor IDs using our updated functions.
    //         $childIds = $this->getChildGroupIds($currentGroupId);
    //         $parentIds = $this->getParentGroupIds($currentGroupId);
            
    //         if (in_array($data['final_id'], $childIds)) {
    //             Notification::make()
    //                 ->title('Invalid Parent Group')
    //                 ->body('The selected parent group is a descendant of the current group.')
    //                 ->danger()
    //                 ->send();
        
    //             throw ValidationException::withMessages([
    //                 'parent_id' => 'Invalid operation: The selected parent group is a descendant of the current group.',
    //             ]);
    //         }
            
    //         if (in_array($data['final_id'], $parentIds)) {
    //             Notification::make()
    //                 ->title('Invalid Parent Group')
    //                 ->body('The selected parent group is an ancestor of the current group.')
    //                 ->danger()
    //                 ->send();
        
    //             throw ValidationException::withMessages([
    //                 'parent_id' => 'Invalid operation: The selected parent group is an ancestor of the current group.',
    //             ]);
    //         }
    //     }
    // }
    
}
