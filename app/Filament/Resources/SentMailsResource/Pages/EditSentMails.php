<?php

namespace App\Filament\Resources\SentMailsResource\Pages;

use App\Filament\Resources\SentMailsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\ApprovalChain;

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

        // Function to replace placeholders
        $replacePlaceholder = function ($content, $placeholder) {
            switch ($placeholder) {
                case '{kode surat}':
                    return str_replace($placeholder, '-replace kode surat berhasil-', $content);
                break;

                case '{nama pengirim}':
                    return str_replace($placeholder, '-replace nama pengirim berhasil-', $content);
                break;

                case '{jabatan}':
                    return str_replace($placeholder, '-replace jabatan berhasil-', $content);
                break;

                case '{NIP}':
                    return str_replace($placeholder, '-replace NIP berhasil-', $content);
                break;
                // Add more cases here for other placeholders if needed
                default:
                    return $content;
            }
        };

        // Apply the replacement function to the content
        $data['content'] = $replacePlaceholder($dom->saveHTML(), '{group id}');
    }

    // Retrieve the first ApprovalChain record where mail_id equals the current record's id and status is 'waiting'
    $approvalChain = ApprovalChain::firstWhere([
        'mail_id' => $this->record->id,
        'status' => 'waiting',
    ]);
    // Ensure the ApprovalChain record exists before accessing its properties
    if ($approvalChain) {
// dd($approvalChain->group_id);

        $data['target_id'] = $approvalChain->group_id;
    } else {


        // Handle the case where no matching ApprovalChain record is found
        // For example, you might set a default value or throw an exception
        $data['target_id'] = null; // or handle as appropriate
    }

    return $data;
}

}
