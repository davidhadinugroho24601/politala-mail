<?php

namespace App\Filament\Resources\SentMailsResource\Pages;

use App\Filament\Resources\SentMailsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\ApprovalChain;
use App\Models\Group;
use App\Models\PathDetail;
use App\Models\MailPath;
use App\Models\MailTemplate;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;
use Google\Client as Google_Client;
use Google\Service\Docs as Google_Service_Docs;
use Google\Service\Docs\Request as Google_Service_Docs_Request;
use Google\Service\Docs\BatchUpdateDocumentRequest as Google_Service_Docs_BatchUpdateDocumentRequest;
use Google\Service\Drive as Google_Service_Drive;
use Google\Service\Drive\DriveFile as Google_Service_Drive_DriveFile;
use Google\Service\Drive\Permission as Google_Service_Drive_Permission;
use Illuminate\Support\Facades\Storage;

class CreateSentMails extends CreateRecord
{
    protected static string $resource = SentMailsResource::class;

    public static function copyOrGenerateGoogleDoc(?string $sourceDocId = null): string
{
    $client = new Google_Client();
    $client->setAuthConfig(storage_path('directed-will-448301-i3-6820f245a961.json'));
    $client->addScope(Google_Service_Docs::DOCUMENTS);
    $client->addScope(Google_Service_Drive::DRIVE);

    $driveService = new Google_Service_Drive($client);

    if ($sourceDocId) {
        // 🔹 Jika ada sourceDocId, salin dokumen
        try {
            $copy = new Google_Service_Drive_DriveFile([
                'name' => 'Copy of Document - ' . uniqid(),
            ]);
            $copiedFile = $driveService->files->copy($sourceDocId, $copy);
            $fileId = $copiedFile->id;
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    } else {
        // 🔹 Jika tidak ada sourceDocId, buat dokumen baru
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => 'New Document ' . now()->format('Y-m-d H:i:s'),
            'mimeType' => 'application/vnd.google-apps.document'
        ]);
        $file = $driveService->files->create($fileMetadata, ['fields' => 'id']);
        $fileId = $file->id;
    }

    // 🔹 Setel izin agar semua orang dapat mengakses dokumen
    try {
        $permission = new Google_Service_Drive_Permission([
            'type' => 'anyone',
            'role' => 'writer', // Bisa diganti 'reader' jika hanya ingin bisa dilihat
        ]);
        $driveService->permissions->create($fileId, $permission);
    } catch (\Exception $e) {
        return 'Error setting permission: ' . $e->getMessage();
    }

    // 🔹 Kembalikan URL dengan `embedded=true`
    return "https://docs.google.com/document/d/{$fileId}/edit?embedded=true";
}




           // Extract Google Doc ID from URL
           function extractGoogleDocId($url) {
            preg_match('/document\/d\/([a-zA-Z0-9_-]+)/', $url, $matches);
            return $matches[1] ?? null;
        }
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

        $this->validateTargetConnection(session('groupID'), $data['final_id'], $data['is_staged']);
        $data['writer_id'] = auth()->id();
        $data['status'] = 'Draft';

        $data['google_doc_link'] = Self::copyOrGenerateGoogleDoc(
            $this->extractGoogleDocId(MailTemplate::find($data['template_id'])?->google_doc_link ?? '')
        );
      

        $record = $this->getModel()::create($data);
       
        
        $currentGroupId = $record->group_id;
        $path = $record->template?->mailPath->where('sender_id', $currentGroupId);

        $data['final_id'] = $path->value('receiver_id');
        $pathId = $path->value('id');

        // dd(PathDetail::where('path_id', $pathId)->pluck('id'));
        $pathDetails = PathDetail::where('path_id', $pathId)
        ->where('authority', '!=', 'skip')
        ->pluck('id')
        ->toArray();

        $pathGroups = PathDetail::where('path_id', $pathId)
        ->where('authority', '!=', 'skip')
        ->pluck('group_id')
        ->toArray();
        // dd($pathDetails);
        $this->createApprovalChain($record->id, $pathDetails);

        if (!empty($pathGroups)) {
                    // Update the record with the first target_id in the path
                    $record->update(['target_id' => $pathGroups[0]]);
        }

        // $status = 'down';

        // $pathIDs = 
        // $childIDs = $this->findShortestPath($currentGroupId, $data['final_id']);

        // if (in_array($record->final_id, $parentIDs)) {
        //     $status = 'up';
        // }





        // if ($record->is_staged === 'yes') {
        //     $pathIDs = $this->findShortestPath($currentGroupId, $data['final_id']);
        //     $this->createApprovalChain($record->id, $pathIDs);
        //     if (!empty($pathIDs)) {
        //         // Update the record with the first target_id in the path
        //         $record->update(['target_id' => $pathIDs[0]]);
        //     }
        // }
        // else{
        //     $pathIDs[] = $data['final_id'];

        //     $this->createApprovalChain($record->id, $pathIDs);
        //     if (!empty($pathIDs)) {
        //         // Update the record with the first target_id in the path
        //         $record->update(['target_id' => $pathIDs[0]]);
        //     }
        // }




        // dd($pathIDs);
        

        return $record;
    }

    // /**
    //  * Create approval chain entries.
    //  */ 
    // private function createApprovalChain($mailId, array $groupIds)
    // {
    //     $approvalChains = array_map(fn ($groupId) => [
    //         'mail_id' => $mailId,
    //         'group_id' => $groupId,
    //     ], $groupIds);
    //     if (!empty($approvalChains)) {
    //         ApprovalChain::insert($approvalChains);
    //     }
    // }


     /**
     * Create approval chain entries.
     */ 
    private function createApprovalChain($mailId, array $paths)
    {
        $pathDetails = PathDetail::whereIn('id', $paths)->get();

        $approvalChains = $pathDetails->map(fn ($path) => [
            'mail_id' => $mailId,
            'group_id' => $path->group_id,
            'path_detail_id' => $path->id,
            'authority' => $path->authority,
        ])->toArray();
            // dd($approvalChains);
        if (!empty($approvalChains)) {
            ApprovalChain::insert($approvalChains);
        }
    }


    /**
     * Validate if the final_id is connected to the sender.
     */
    private function validateTargetConnection($currentGroupId, $finalId, $isStaged)
    {
           
        $connectedGroups = array_merge(
            $this->findShortestPath($currentGroupId, $finalId),
            $this->findShortestPath($finalId, $currentGroupId) 
        );

        $finalGroupParent = Group::where('id', $finalId)->value('parent_id');
        // dd(in_array($finalGroupParent, $connectedGroups) || $finalId === $currentGroupId);
        
        $is_parent = !(in_array($finalGroupParent, $connectedGroups) || $finalId === $currentGroupId);

       

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

        if ($is_parent && $isStaged === 'no') {
            Notification::make()
                ->title('Invalid Target')
                ->body('The selected target is unreachable.')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'final_id' => 'Invalid operation: The selected group is not connected.',
            ]);
        }
    }

}
