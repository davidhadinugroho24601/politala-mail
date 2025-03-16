<?php

namespace App\Filament\Resources\SentMailsResource\Pages;

use App\Filament\Resources\SentMailsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\ApprovalChain;
use App\Models\MailCode;
use App\Models\Division;
use App\Models\Group;
use Google\Client as Google_Client;
use Google\Service\Docs as Google_Service_Docs;
use Google\Service\Docs\Request as Google_Service_Docs_Request;
use Google\Service\Docs\BatchUpdateDocumentRequest as Google_Service_Docs_BatchUpdateDocumentRequest;
use Google\Service\Drive as Google_Service_Drive;
use Google\Service\Drive\DriveFile as Google_Service_Drive_DriveFile;
use Google\Service\Drive\Permission as Google_Service_Drive_Permission;
use Illuminate\Support\Facades\Storage;
class EditSentMails extends EditRecord
{
    protected static string $resource = SentMailsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

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
    // Extract Google Doc ID from URL
    function extractGoogleDocId($url) {
        preg_match('/document\/d\/([a-zA-Z0-9_-]+)/', $url, $matches);
        return $matches[1] ?? null;
    }

    protected function mutateFormDataBeforeSave(array $data): array
{

    // $docLink = $this->record->google_doc_link;
    
    // if (!empty($docLink)) {
    //     $pdfPath = $this->saveGoogleDocAsPdf($docLink);
    //     // Save the path if the conversion was successful
    //     if (!str_starts_with($pdfPath, 'Error:')) {
    //         $this->record->pdf_path = $pdfPath;
    //     }
    // }
    
    if (isset($data['content'])) {
        // dd('tes');
        // Load the HTML content into DOMDocument
        $dom = new \DOMDocument();
        @$dom->loadHTML($data['content'], LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

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


// Function to replace placeholders dynamically
$replacePlaceholders = function ($content) {
    $divisionAcronym = Group::where('id', session('groupID'))
    ->with('division') // Ensure the relationship is loaded
    ->first()
    ?->division
    ?->acronym;

    $enabledMailCode = MailCode::where('status', 'enabled')->value('code') ?? '-replace kode surat gagal-';
    // $divisionAcronym  = Division::where('')->value('acronym') is division of Group:: where group belongsTo Division
    $placeholders = [
        '{kode surat}' => $enabledMailCode,
        '{nama pengirim}' => '-replace nama pengirim berhasil-',
        '{jabatan}' => '-replace jabatan berhasil-',
        '{NIP}' => '-replace NIP berhasil-',
        '{akronim divisi}' => $divisionAcronym,
        '{tanggal}' => date('d'), // Replace with current day
        '{bulan}' => date('m'),   // Replace with current month
        '{tahun}' => date('Y'),
    ];

    return str_replace(array_keys($placeholders), array_values($placeholders), $content);
};

// Apply the replacement function to the content
$data['content'] = $replacePlaceholders($dom->saveHTML());

    }

    // Retrieve the first ApprovalChain record where mail_id equals the current record's id and status is 'waiting'
    $approvalChain = ApprovalChain::firstWhere([
        'mail_id' => $this->record->id,
        'status' => 'waiting',
    ]);

    $deniedChain = ApprovalChain::firstWhere([
        'mail_id' => $this->record->id,
        'status' => 'denied',
    ]);

    // dd($approvalChain);
    // Ensure the ApprovalChain record exists before accessing its properties
    if ($approvalChain) {
// dd($approvalChain->group_id);

        $data['target_id'] = $approvalChain->group_id;
    } elseif($deniedChain) {
        $data['target_id'] = $deniedChain->group_id;
    }
    else{


        // Handle the case where no matching ApprovalChain record is found
        // For example, you might set a default value or throw an exception
        $data['target_id'] = null; // or handle as appropriate
    }

    return $data;
}

}
