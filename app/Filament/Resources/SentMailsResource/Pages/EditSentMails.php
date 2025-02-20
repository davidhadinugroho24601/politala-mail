<?php

namespace App\Filament\Resources\SentMailsResource\Pages;

use App\Filament\Resources\SentMailsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\ApprovalChain;
use App\Models\MailCode;
use App\Models\Division;
use App\Models\Group;

class EditSentMails extends EditRecord
{
    protected static string $resource = SentMailsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // protected function beforeSave(): void
    // {
       
    // }
  

    protected function mutateFormDataBeforeSave(array $data): array
{
    if (isset($data['content'])) {
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
