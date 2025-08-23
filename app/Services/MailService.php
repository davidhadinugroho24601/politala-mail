<?php
namespace App\Services;

use App\Models\Report;
use App\Models\ApprovalChain;
use App\Models\MailTemplate;
use Filament\Notifications\Notification;
use App\Models\Mail;
use App\Models\User;
use App\Models\Group;
use App\Models\MailCode;
use App\Notifications\ApprovalProcessed;
use Google\Client as Google_Client;
use Google\Service\Docs as Google_Service_Docs;
use Google\Service\Docs\Request as Google_Service_Docs_Request;
use Google\Service\Docs\BatchUpdateDocumentRequest as Google_Service_Docs_BatchUpdateDocumentRequest;
use Google\Service\Drive as Google_Service_Drive;
use Google\Service\Drive\DriveFile as Google_Service_Drive_DriveFile;
use Google\Service\Drive\Permission as Google_Service_Drive_Permission;
use Illuminate\Support\Facades\Storage;
use Google_Service_Docs_ReplaceAllTextRequest;
use Illuminate\Support\Facades\Auth;
use ZipArchive;
use Illuminate\Http\Response;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Str;
use setasign\Fpdi\PdfReader;
use App\Models\CodeList;
class MailService
{

   
    
    public function downloadAllAsZip()
    {
        $zipFileName = 'documents_' . time() . '.zip';
        $zipStoragePath = 'public/' . $zipFileName; // Laravel storage path
        $zipFullPath = storage_path('app/' . $zipStoragePath); // Full system path
    
        // Ensure directory exists
        if (!file_exists(dirname($zipFullPath))) {
            mkdir(dirname($zipFullPath), 0777, true);
        }
    
        $zip = new ZipArchive;
        if ($zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['error' => 'Could not create ZIP file'], 500);
        }
    
        // Get all files inside storage/app/public/google_docs/
        $files = Storage::files('public/google_docs');
    
        if (empty($files)) {
            return response()->json(['error' => 'No files found in google_docs directory'], 404);
        }
    
        foreach ($files as $file) {
            $fullPath = storage_path('app/' . $file); // Convert to full path
            if (file_exists($fullPath)) {
                $zip->addFile($fullPath, basename($file));
            } else {
                \Log::error("File not found: $fullPath"); // Log missing files
            }
        }
    
        $zip->close();
    
        // Check if the ZIP file actually exists
        if (!file_exists($zipFullPath)) {
            \Log::error("ZIP file was not created: $zipFullPath"); // Log the error
            return response()->json(['error' => 'ZIP file was not created'], 500);
        }
    
        return response()->download($zipFullPath)->deleteFileAfterSend(true);
    }
    
        
    

    
    function initGoogleDocsService(): Google_Service_Docs {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path(config('globals.jwt_token')));
        $client->addScope(Google_Service_Docs::DOCUMENTS);
        return new Google_Service_Docs($client);
    }
    
    function generateMailPlaceholders($record): array {
            $template = $record->template;
            $archiveClassification = $template->archive_classification;
            // dd($template);
            $group = Group::where('id', $record->group_id)->with('division', 'groupDetails.user')->first();
            $divisionAcronym = $group?->division?->acronym ?? 'Akronim Divisi Tidak Diketahui';
            $divisionName = $group?->division?->name ?? 'Nama Divisi Tidak Diketahui';
            $divisionCode = $group?->division?->division_code ?? 'Kode Divisi Tidak Diketahui';

            $releasedMail = Mail::where('status', 'Submitted')
                ->whereHas('template', function ($query) {
                    $query->where('name', '!=', 'Disposisi');
                })
                ->count() + 1;

            $releasedMail = str_pad($releasedMail, 3, '0', STR_PAD_LEFT);


            $writerGroupName = $group?->name ?? 'Jabatan Pengirim Tidak Diketahui';
            $recipientGroupName = Group::where('id', $record->final_id)->value('name') ?? 'Jabatan Penerima Tidak Diketahui';
            $dispositionName = $record?->disposition?->name ?? 'Disposisi Kosong';

            $writer = Auth::user();
            $peerIds = $writer->groupDetails->pluck('group.peer_id')->filter()->unique();

            if (in_array($record->group_id, $peerIds->toArray())) {
                $writerDetail = $group->groupDetails->first();
                $writer = $writerDetail?->user;
            }

            $recipient = $record?->recipient ?? optional($record?->finalTarget?->groupDetail?->first())->user;
            // dd($recipient->name);

             // Array nama bulan dalam Bahasa Indonesia
    $bulanIndonesia = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember',
    ];

    $currentMonth = date('m');
    $namaBulan = $bulanIndonesia[$currentMonth] ?? $currentMonth;

            return [
                '{disposisi}' => (string) $dispositionName,
                '{surat terbit}' => (string) $releasedMail,
                '{nama pengirim}' => $writer?->name ?? 'Pengirim Tidak Diketahui',
                '{nama penerima}' => $recipient?->name ?? 'Penerima Tidak Diketahui',
                '{jabatan pengirim}' => $writerGroupName,
                '{jabatan penerima}' => $recipientGroupName,
                '{NIP Pengirim}' => $writer?->NIP ?? 'NIP Pengirim Tidak Diketahui',
                '{NIP Penerima}' => $recipient?->NIP ?? 'NIP Penerima Tidak Diketahui',
                '{NIDN Pengirim}' => $writer?->NIDN ?? 'NIDN Pengirim Tidak Diketahui',
                '{NIDN Penerima}' => $recipient?->NIDN ?? 'NIDN Penerima Tidak Diketahui',
                '{akronim divisi}' => $divisionAcronym,
                '{nama divisi}' => $divisionName,
                '{kode divisi}' => $divisionCode,
                '{tanggal}' => date('d'),
                '{bulan}' => $namaBulan,
                '{tahun}' =>  date('Y'),
                '{klasifikasi arsip}'=>$archiveClassification ?? 'Klasifikasi Arsip Kosong',
            ];
        }

    
    function replacePlaceholdersInGoogleDoc($documentId, $record) {
        $service = $this->initGoogleDocsService();
        $placeholders = $this->generateMailPlaceholders($record);
        $requests = [];
        foreach ($placeholders as $placeholder => $replacement) {
            $requests[] = new Google_Service_Docs_Request([
                'replaceAllText' => new Google_Service_Docs_ReplaceAllTextRequest([
                    'containsText' => ['text' => $placeholder, 'matchCase' => true],
                    'replaceText' => $replacement,
                ]),
            ]);
        }
        $service->documents->batchUpdate($documentId, new Google_Service_Docs_BatchUpdateDocumentRequest([
            'requests' => $requests,
        ]));
        return "https://docs.google.com/document/d/{$documentId}/edit?embedded=true";
    }
    
    function assignCode($documentId, $record) {
    $service = $this->initGoogleDocsService();
    $placeholders = $this->generateMailPlaceholders($record);
// dd($record->template);
    if ($record->template->name == 'Disposisi') {
        $parsedMailCode = '-';
    } elseif (empty($record->assignedCode)) {
        $enabledMailCode = MailCode::where('status', 'enabled')->value('code');
        $parsedMailCode = strtr($enabledMailCode, $placeholders);
        CodeList::create([
            'code' => $parsedMailCode,
            'mail_id' => $record->id,
        ]);
    } else {
        $parsedMailCode = $record->assignedCode->code;
    }

    $requests = [
        new Google_Service_Docs_Request([
            'replaceAllText' => new Google_Service_Docs_ReplaceAllTextRequest([
                'containsText' => ['text' => '{kode surat}', 'matchCase' => true],
                'replaceText' => $parsedMailCode ?? 'Replace Kode Surat Gagal',
            ]),
        ]),
    ];

    $service->documents->batchUpdate($documentId, new Google_Service_Docs_BatchUpdateDocumentRequest([
        'requests' => $requests,
    ]));

    return "https://docs.google.com/document/d/{$documentId}/edit?embedded=true";
}


    public static function copyOrGenerateGoogleDoc(?string $sourceDocId = null): string
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path(config('globals.jwt_token')));
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




function saveGoogleDocAsPdf($googleDocUrl, $record) {
    $docId = $this->extractGoogleDocId($googleDocUrl);
    if (!$docId) {
        return 'Error: Invalid Google Doc URL';
    }

    $client = new \Google_Client();
    $client->setAuthConfig(storage_path(config('globals.jwt_token')));
    $client->addScope(\Google_Service_Drive::DRIVE);

    $driveService = new \Google_Service_Drive($client);

    try {
        // Export Google Doc as PDF
        $response = $driveService->files->export($docId, 'application/pdf', ['alt' => 'media']);

        // Generate unique filename
        $fileName = 'google_docs/' . uniqid('document_', true) . '.pdf';
        $filePath = storage_path('app/public/' . $fileName);

        // Save PDF to storage
        Storage::disk('public')->put($fileName, $response->getBody());

        // Open PDF with FPDI (no visual modification)
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($filePath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplId = $pdf->importPage($pageNo);
            $pdf->addPage();
            $pdf->useTemplate($tplId);
        }

        // ✅ Set metadata (optional)
        $pdf->SetTitle('Google Doc Export');
        $pdf->SetAuthor('Laravel App');
        $pdf->SetSubject('Hash Validation');

        // Save final PDF first
        $pdf->Output($filePath, 'F');

        // ✅ Now compute the hash AFTER saving
        $fileHash = hash_file('sha256', $filePath);

        // Update DB record with the correct hash
        $record->update(['file_hash' => $fileHash]);

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

    public function declineMailWithNote($record, array $data)
    {
        // Update the ApprovalChain with the user's notes and set status to 'denied'
        ApprovalChain::where('mail_id', $record->id)
            ->where('group_id', session('groupID'))
            ->update([
                'status' => 'denied',
                'notes' => $data['notes'],
            ]);

        // Delete the file if it exists
        if ($record->pdf_path && Storage::exists($record->pdf_path)) {
            Storage::delete($record->pdf_path);
        }

        // Change mail status back to "Draft"
        $record->update([
            'status' => 'Draft',
            'notes' => $data['notes'],
            'pdf_path' => null,
        ]);


        // Send a success notification
        Notification::make()
            ->title('Mail declined with notes successfully! Status set to Draft.')
            ->success()
            ->send();
    }

    public function declineMail($record)
    {
        // Update the ApprovalChain with the user's notes and set status to 'denied'
        ApprovalChain::where('mail_id', $record->id)
            ->where('group_id', session('groupID'))
            ->update([
                'status' => 'trashed',
                // 'notes' => $data['notes'],
            ]);

        // // Change mail status back to "Draft"
        // $record->update([
        //     'status' => 'Draft',
        //     'notes' => $data['notes'],
        // ]);

        // Send a success notification
        Notification::make()
            ->title('Mail rejected successfully!')
            ->success()
            ->send();
    }

    public function approveMail($record)
    {
        $groupID = session('groupID'); // Get the current group ID
        $mailTemplate = MailTemplate::where('id', $record->template_id)->value('name');
        $mail = $record;

        $currentApproval = ApprovalChain::where('mail_id', $record->id)
            ->where('group_id', $groupID)
            ->orderBy('id')
            ->first();

        if (!$currentApproval) {
            return;
        }

        // Approve the current step
        $currentApproval->update(['status' => 'approved']);



        // Find the next approval step
        $nextApproval = ApprovalChain::where('mail_id', $record->id)
            ->where('id', '>', $currentApproval->id)
            ->orderBy('id')
            ->first();

        if ($nextApproval) {
            // Update target_id to the next approval step
            $record->update(['target_id' => $nextApproval->group_id]);
        } else {
            // If no next step, mark as finished
            ApprovalChain::where('mail_id', $record->id)
                ->where('group_id', $groupID)
                ->update(['status' => 'finished']);

                if ($mailTemplate == 'Disposisi' && $record->disposition?->name == 'Mohon dapat mewakili') {
                    // Find the MailTemplate where the name is 'Surat Tugas'
                    $suratTugasTemplate = MailTemplate::where('name', 'Surat Tugas')->first();
                    
                    if ($suratTugasTemplate) {
                        // Create a new mail with the found template and conditions
                        $newMail = Mail::create([
                            'template_id' => $suratTugasTemplate->id,
                            'subject' => 'Surat Tugas',
                            'target_id' => session('groupID'),
                            'final_id' => session('groupID'),
                            'group_id' => session('groupID'),
                            'writer_id' => $record->writer_id,
                            'status' => 'Draft',
                        ]);

                    $newMail->update(['google_doc_link' => $this->copyOrGenerateGoogleDoc(
                    $this->extractGoogleDocId(MailTemplate::find($newMail['template_id'])?->google_doc_link ?? '')
                    )]);
                    // $newMail->update(['pdf_path' => $this->saveGoogleDocAsPdf($newMail['google_doc_link']), $newMail]);
                    // dd();
                        // Create an ApprovalChain entry for the new mail
                        // ApprovalChain::create([
                        //     'mail_id' => $newMail->id,
                        //     'group_id' => session('groupID'),
                        //     'status' => 'waiting',
                        // ]);
                    }
                }
        
        
            }

        // Notify all users in the group
        $groupUsers = User::whereIn('id', function ($query) use ($record) {
            $query->select('user_id')
                ->from('group_details')
                ->where('group_id', $record->group_id);
        })->get();

        foreach ($groupUsers as $user) {
            $user->notify(new ApprovalProcessed('Approved', $record));
        }

// cari user berdasarkan writer_id di mail
$writer = User::find($record->writer_id);

// default nomor HP kalau nggak ada
$phone = $writer?->phone ?? null;

if ($phone) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
            'target'  => $phone, // << diisi dari user.phone
            'message' => "Hello {$writer->name},\n\n".
                     "Your mail with subject \"{$record->subject}\" ".
                     "has been {$record->status}.\n".
                     "You can view it here: " . url('/admin/received-mails/' . $record->id . '/edit'),
        ),
        CURLOPT_HTTPHEADER => array(
            'Authorization: FtWixrC6FjYqSaH7cnk7' // nanti ganti pakai config()
        ),
    ));

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        $error_msg = curl_error($curl);
    }
    curl_close($curl);
}


        Notification::make()
            ->title('Approval processed successfully!')
            ->success()
            ->send();



    }

 
    public function sendMail($record)
    {
        $docLink = $record->google_doc_link;
        $googleDocId = $this->extractGoogleDocId($docLink);
        
        $this->replacePlaceholdersInGoogleDoc($googleDocId, $record); // no need to return anything
        $updatedDocLink = $this->assignCode($googleDocId, $record); // this one returns updated link
        
        $record->update([
            'google_doc_link' => $updatedDocLink,
            'released' => 'yes',
        ]);
        
        if (!empty($docLink)) {
            $pdfPath = $this->saveGoogleDocAsPdf($docLink, $record);
            // Save the path if the conversion was successful
            if (!str_starts_with($pdfPath, 'Error:')) {
                $record->pdf_path = $pdfPath;
            }
        }
        
        // Update mail status
        $record->update(['status' => 'Submitted']);

        if (!$record->released) {
            $record->update(['released' => true]);
            
            $firstReport = Report::first();
            if ($firstReport) {
                $firstReport->increment('created_mails');
            } else {
                Report::create(['created_mails' => 1]);
            }
        }
       
        // Reset denied approvals
        ApprovalChain::where('mail_id', $record->id)
            ->where('status', 'denied')
            ->update(['status' => 'waiting']);

        // Notify user
        Notification::make()
            ->title('Mail sent successfully!')
            ->success()
            ->send();


           
    }
}
