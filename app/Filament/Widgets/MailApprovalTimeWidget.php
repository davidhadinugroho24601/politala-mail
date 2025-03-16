<?php

namespace App\Filament\Widgets;

use App\Models\Mail;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\Auth;

class MailApprovalTimeWidget extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getCards(): array
    {
        // $user = Auth::user();
        $groupID = session('groupID'); 
        // $isAdmin = $user->role === 'admin';

        // Calculate the average time to acceptance (status = finished)
        $acceptedAvgDays = Mail::whereHas('approvalChains', function ($query) use ($groupID) {
            $query->where('status', 'finished');
            if ($groupID !== 'admin') {
                $query->where('group_id', $groupID);
            }
        })->whereNotNull('created_at')
          ->whereNotNull('updated_at')
          ->get()
          ->map(fn($mail) => $mail->created_at->diffInDays($mail->updated_at))
          ->average() ?? 0;

        // Calculate the average time to rejection (status = trashed)
        $rejectedAvgDays = Mail::whereHas('approvalChains', function ($query) use ($groupID) {
            $query->where('status', 'trashed');
            if ($groupID !== 'admin') {
                $query->where('group_id', $groupID);
            }
        })->whereNotNull('created_at')
          ->whereNotNull('updated_at')
          ->get()
          ->map(fn($mail) => $mail->created_at->diffInDays($mail->updated_at))
          ->average() ?? 0;

        return [
            Card::make('Rata-rata Hari Sampai Disetujui', number_format($acceptedAvgDays, 2) . ' hari')
                ->description('Waktu yang dibutuhkan hingga surat disetujui')
                ->color('success'),

            Card::make('Rata-rata Hari Sampai Ditolak', number_format($rejectedAvgDays, 2) . ' hari')
                ->description('Waktu yang dibutuhkan hingga surat ditolak')
                ->color('danger'),
        ];
    }
}
