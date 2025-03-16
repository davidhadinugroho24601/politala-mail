<?php

namespace App\Filament\Resources\MailTemplateResource\Pages;

use App\Filament\Resources\MailTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Google\Client as Google_Client;
use Google\Service\Docs as Google_Service_Docs;
use Google\Service\Drive as Google_Service_Drive;
use Google\Service\Drive\DriveFile as Google_Service_Drive_DriveFile;
use Google\Service\Drive\Permission as Google_Service_Drive_Permission;
class CreateMailTemplate extends CreateRecord
{
    protected static string $resource = MailTemplateResource::class;
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
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
{


    $data['google_doc_link'] = Self::generateGoogleDoc();

    if (isset($data['template'])) {
        // Load the HTML content into DOMDocument
        $dom = new \DOMDocument();
        @$dom->loadHTML($data['template'], LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Loop through all <figcaption> tags
        $figcaptions = $dom->getElementsByTagName('figcaption');
        for ($i = $figcaptions->length - 1; $i >= 0; $i--) {
            $figcaption = $figcaptions->item($i);

            // Traverse up the parent tree
            $parent = $figcaption->parentNode;
            while ($parent) {
                // Check if the node is a DOMElement and has the attribute "data-trix-content-type"
                if ($parent instanceof \DOMElement &&
                    $parent->hasAttribute('data-trix-content-type') &&
                    str_contains($parent->getAttribute('data-trix-content-type'), 'image')) {
                    // Remove the <figcaption> tag
                    $figcaption->parentNode->removeChild($figcaption);
                    break;
                }
                $parent = $parent->parentNode; // Move up the DOM tree
            }
        }

        // Save the modified HTML back to the template field
        $data['template'] = $dom->saveHTML();
    }

    // Create the record
    $record = static::getModel()::create($data);

    return $record;
}



}
