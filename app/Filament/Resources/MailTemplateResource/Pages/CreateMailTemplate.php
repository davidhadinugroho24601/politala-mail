<?php

namespace App\Filament\Resources\MailTemplateResource\Pages;

use App\Filament\Resources\MailTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMailTemplate extends CreateRecord
{
    protected static string $resource = MailTemplateResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
{
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
