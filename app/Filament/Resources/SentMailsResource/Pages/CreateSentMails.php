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
    function saveGoogleDocAsPdf($googleDocUrl) {
        $docId = $this->extractGoogleDocId($googleDocUrl);
        if (!$docId) {
            return 'Error: Invalid Google Doc URL';
        }
    
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('directed-will-448301-i3-d1dc6de8a986.json'));
        $client->addScope(Google_Service_Drive::DRIVE);
    
        $driveService = new Google_Service_Drive($client);
    
        try {
            // Export Google Doc as PDF
            $response = $driveService->files->export($docId, 'application/pdf', ['alt' => 'media']);
    
            // Generate unique filename
            $fileName = 'google_docs/' . uniqid('document_', true) . '.pdf';
            $filePath = storage_path('app/public/' . $fileName);
    
            // Save PDF to storage
            Storage::disk('public')->put($fileName, $response->getBody());
            // dd
            return 'storage/' . $fileName;
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }


    
    function copyGoogleDocContent($sourceDocId, $targetDocId) {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('directed-will-448301-i3-d1dc6de8a986.json'));
        $client->addScope([Google_Service_Docs::DOCUMENTS, Google_Service_Drive::DRIVE]);
    
        $docsService = new Google_Service_Docs($client);
    
        // Get source document content
        $sourceDoc = $docsService->documents->get($sourceDocId);
        $content = '';
    
        foreach ($sourceDoc->getBody()->getContent() as $element) {
            $paragraph = $element->getParagraph();
            if (null !== $paragraph) {
                foreach ($paragraph->getElements() as $textRun) {
                    $textContent = $textRun->getTextRun();
                    if (null !== $textContent) {
                        $content .= $textContent->getContent();
                    }
                }
            }
            $content .= "\n";
        }
    
        // Get the actual document length
        $targetDoc = $docsService->documents->get($targetDocId);
        $endIndex = $targetDoc->getBody()->getContent();
        $docLength = count($endIndex) > 0 ? end($endIndex)->getEndIndex() : 1; // Get last index
    
        // Clear target document and insert new content
        $requests = [
            // new Google_Service_Docs_Request(['deleteContentRange' => ['range' => ['startIndex' => 1, 'endIndex' => $docLength]]]), // Corrected endIndex
            new Google_Service_Docs_Request(['insertText' => ['location' => ['index' => 1], 'text' => $content]])
        ];
    
        $batchUpdateRequest = new Google_Service_Docs_BatchUpdateDocumentRequest(['requests' => $requests]);
        $docsService->documents->batchUpdate($targetDocId, $batchUpdateRequest);
    }
    
    // Extract Google Doc ID from URL
function extractGoogleDocId($url) {
    preg_match('/document\/d\/([a-zA-Z0-9_-]+)/', $url, $matches);
    return $matches[1] ?? null;
}

    
    
    
    public static function generateGoogleDoc(): string
    {
        
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('directed-will-448301-i3-d1dc6de8a986.json'));
        $client->addScope(Google_Service_Docs::DOCUMENTS);
        $client->addScope(Google_Service_Drive::DRIVE_FILE);
        $client->addScope(Google_Service_Drive::DRIVE);
    
        $service = new Google_Service_Docs($client);
        $driveService = new Google_Service_Drive($client);
    
        // Create a new Google Doc
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => 'New Document ' . now()->format('Y-m-d H:i:s'),
            'mimeType' => 'application/vnd.google-apps.document'
        ]);
        $file = $driveService->files->create($fileMetadata, ['fields' => 'id']);
    
        $fileId = $file->id;
    
        // 🔹 Set file permissions: Anyone can edit
        $permission = new Google_Service_Drive_Permission([
            'type' => 'anyone',
            'role' => 'writer',
        ]);
        $driveService->permissions->create($fileId, $permission);
    
        // Return the embedded URL
        return "https://docs.google.com/document/d/{$fileId}/edit?embedded=true";
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
        $data['google_doc_link'] = Self::generateGoogleDoc();
        $data['writer_id'] = auth()->id();
        $data['status'] = 'Draft';

       // Get source and target Google Docs from database
        $sourceDocId = $this->extractGoogleDocId(MailTemplate::find($data['template_id'])?->google_doc_link ?? '');
        $targetDocId = $this->extractGoogleDocId($data['google_doc_link'] ?? '');

        if ($sourceDocId && $targetDocId) {
            $this->copyGoogleDocContent($sourceDocId, $targetDocId);
        }

        if (!empty($data['google_doc_link'])) {
            $pdfPath = $this->saveGoogleDocAsPdf($data['google_doc_link']);
            
            // Save the path if the conversion was successful
            if (!str_starts_with($pdfPath, 'Error:')) {
                $data['pdf_path'] = $pdfPath;
            }
        }
        

        $record = $this->getModel()::create($data);
       
        
        $currentGroupId = $record->group_id;
        // $status = 'down';

        
        // $childIDs = $this->findShortestPath($currentGroupId, $data['final_id']);

        // if (in_array($record->final_id, $parentIDs)) {
        //     $status = 'up';
        // }

        if ($record->is_staged === 'yes') {
            $pathIDs = $this->findShortestPath($currentGroupId, $data['final_id']);
            $this->createApprovalChain($record->id, $pathIDs);
            if (!empty($pathIDs)) {
                // Update the record with the first target_id in the path
                $record->update(['target_id' => $pathIDs[0]]);
            }
        }
        else{
            $pathIDs[] = $data['final_id'];

            $this->createApprovalChain($record->id, $pathIDs);
            if (!empty($pathIDs)) {
                // Update the record with the first target_id in the path
                $record->update(['target_id' => $pathIDs[0]]);
            }
        }
        // dd($pathIDs);
        

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
