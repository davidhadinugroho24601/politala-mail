<?php

namespace App\Filament\Widgets;

use App\Models\Mail;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class MailStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Mail Approval Statuses';

    protected function getData(): array
    {
        // $user = Auth::user();
        $groupID = session('groupID'); 

        $accepted = Mail::whereHas('approvalChains', function ($query) use ($groupID) {
            $query->where('status', 'finished');
            if ($groupID !== 'admin') {
                $query->where('group_id', $groupID);
            }
        })->count();

        $rejected = Mail::whereHas('approvalChains', function ($query) use ($groupID) {
            $query->where('status', 'trashed');
            if ($groupID !== 'admin') {
                $query->where('group_id', $groupID);
            }
        })->count();

        $onProgress = Mail::whereHas('approvalChains', function ($query) use ($groupID) {
            $query->whereNotIn('status', ['finished', 'trashed']);
            if ($groupID !== 'admin') {
                $query->where('group_id', $groupID);
            }
        })->count();

        return [
            'labels' => ['Accepted', 'Rejected', 'On Progress'],
            'datasets' => [
                [
                    'data' => [$accepted, $rejected, $onProgress],
                    'backgroundColor' => ['#27ae60', '#c0392b', '#f39c12'],
                    'borderColor' => ['#2ecc71', '#e74c3c', '#f1c40f'],
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie'; // You can change it to 'doughnut' if preferred
    }
}
