<?php

namespace App\Filament\Resources\MailCodesResource\Pages;

use App\Filament\Resources\MailCodesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\MailCodeDetail;

class CreateMailCodes extends CreateRecord
{
    protected static string $resource = MailCodesResource::class;

}
