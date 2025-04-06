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
    
        
    

    function replacePlaceholdersInGoogleDoc($documentId, $record) {

        // Initialize Google Client
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('directed-will-448301-i3-6820f245a961.json'));
        $client->addScope(Google_Service_Docs::DOCUMENTS);
    
        $service = new Google_Service_Docs($client);
        
        // Fetch document content
        $document = $service->documents->get($documentId);
    
        // Ambil data grup beserta divisi
        $group = Group::where('id', session('groupID'))
            ->with('division')
            ->first();

        $divisionAcronym = $group?->division?->acronym ?? 'Akronim Divisi Tidak Diketahui';
        $divisionName = $group?->division?->name ?? 'Nama Divisi Tidak Diketahui';
        $divisionCode = $group?->division?->division_code ?? 'Kode Divisi Tidak Diketahui';
            

        $enabledMailCode = MailCode::where('status', 'enabled')->first()->value('code') ?? 'Replace Kode Surat Gagal';
        $releasedMail = Mail::where('status', 'Submitted')->count() + 1;
        
        $writerGroupName = Group::where('id', session('groupID'))->value('name') ?? 'Jabatan Pengirim Tidak Diketahui';
        $recipientGroupName = Group::where('id', $record->final_id)->value('name') ?? 'Jabatan Penerima Tidak Diketahui';

        $dispositionName = $record?->disposition?->name ?? 'Disposisi Kosong';
        // dd($dispositionName);

        $writer = Auth::user();
        $recipient = $record?->recipient;

        $writerName = $writer?->name ?? 'Pengirim Tidak Diketahui';
        $recipientName = $recipient?->name ?? 'Penerima Tidak Diketahui';

        $writerNIP = $writer?->NIP ?? 'NIP Pengirim Tidak Diketahui';
        $recipientNIP = $recipient?->NIP ?? 'NIP Penerima Tidak Diketahui';
        
        $writerNIDN = $writer?->NIDN ?? 'NIDN Pengirim Tidak Diketahui';
        $recipientNIDN = $recipient?->NIDN ?? 'NIDN Penerima Tidak Diketahui';

        // dd($recipientGroup);
        $placeholders = [
            '{disposisi}' => (string) $dispositionName,
            '{kode surat}' => (string) $enabledMailCode,
            '{surat terbit}' => (string) $releasedMail,
            '{nama pengirim}' => $writerName, // Get sender from session
            '{nama penerima}' => $recipientName, // Get recipient from $this->record
            '{jabatan pengirim}' => $writerGroupName,
            '{jabatan penerima}' => $recipientGroupName,
            '{NIP Pengirim}' => $writerNIP,
            '{NIP Penerima}' => $recipientNIP,
            '{NIDN Pengirim}' => $writerNIDN,
            '{NIDN Penerima}' => $recipientNIDN,
            '{akronim divisi}' => (string) $divisionAcronym,
            '{nama divisi}' => (string) $divisionName,
            '{kode divisi}' => (string) $divisionCode,
            '{tanggal}' => date('d'),
            '{bulan}' => date('m'),
            '{tahun}' => date('Y'),
        ];
        
        
        // Prepare batch requests to replace placeholders
        $requests = [];
        foreach ($placeholders as $placeholder => $replacement) {
            $requests[] = new Google_Service_Docs_Request([
                'replaceAllText' => new Google_Service_Docs_ReplaceAllTextRequest([
                    'containsText' => ['text' => $placeholder, 'matchCase' => true],
                    'replaceText' => $replacement,
                ]),
            ]);
        }
    
        // Apply changes to Google Doc
        $service->documents->batchUpdate($documentId, new Google_Service_Docs_BatchUpdateDocumentRequest([
            'requests' => $requests,
        ]));
    
        return "https://docs.google.com/document/d/{$documentId}/edit?embedded=true";
    }
    

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


    function saveGoogleDocAsPdf($googleDocUrl, $record) {
        $docId = $this->extractGoogleDocId($googleDocUrl);
        if (!$docId) {
            return 'Error: Invalid Google Doc URL';
        }
    
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('directed-will-448301-i3-6820f245a961.json'));
        $client->addScope(Google_Service_Drive::DRIVE);
    
        $driveService = new Google_Service_Drive($client);
    
        try {
            // Export Google Doc as PDF
            $response = $driveService->files->export($docId, 'application/pdf', ['alt' => 'media']);
    
            // Generate unique filename
            $fileName = 'google_docs/' . uniqid('document_', true) . '.pdf';
            $filePath = storage_path('app/public/' . $fileName);
    
            // Save original PDF to storage
            Storage::disk('public')->put($fileName, $response->getBody());
    
            // Generate a unique hidden message
            $hiddenMessage = Str::uuid(); // Example: Hidden ID: a3b7c2d0-1234-5678-9abc-def012345678
    
            // Add hidden text using FPDF/FPDI
            $pdf = new Fpdi();
            $pdf->setSourceFile(storage_path('app/public/' . $fileName));
            $tplId = $pdf->importPage(1);
            $pdf->addPage();
            $pdf->useTemplate($tplId);
    
            // Set transparent text color
            $pdf->SetTextColor(255, 255, 255); // White text (invisible)
            $pdf->SetXY(10, 10);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Write(0, $hiddenMessage);
            $record->update(['hidden_code' => $hiddenMessage]);
    
            // Save the modified PDF
            $pdf->Output(storage_path('app/public/' . $fileName), 'F');
    
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
                            'status' => 'Submitted',
                        ]);

                    $newMail->update(['google_doc_link' => $this->copyOrGenerateGoogleDoc(
                    $this->extractGoogleDocId(MailTemplate::find($newMail['template_id'])?->google_doc_link ?? '')
                    )]);
                    $newMail->update(['pdf_path' => $this->saveGoogleDocAsPdf($newMail['google_doc_link']), $newMail]);
                    // dd();
                        // Create an ApprovalChain entry for the new mail
                        ApprovalChain::create([
                            'mail_id' => $newMail->id,
                            'group_id' => session('groupID'),
                            'status' => 'waiting',
                        ]);
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

        Notification::make()
            ->title('Approval processed successfully!')
            ->success()
            ->send();
    }


    public function sendMail($record)
    {
        $docLink = $record->google_doc_link;
        $googleDocId = $this->extractGoogleDocId($docLink);
        $updatedDocLink = $this->replacePlaceholdersInGoogleDoc($googleDocId, $record);
        
        $record->update(['google_doc_link' => $updatedDocLink]);

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
