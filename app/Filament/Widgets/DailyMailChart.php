<?php

namespace App\Filament\Widgets;

use App\Models\Mail;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class DailyMailChart extends ChartWidget
{
    protected static ?string $heading = 'Mails Created This Week';

    protected function getData(): array
    {
        $startOfWeek = Carbon::now()->startOfWeek(); 
        $labels = [];
        $data = [];
        $groupID = session('groupID'); 
        // $user = Auth::user(); 
        // $isAdmin = $user->role === 'admin'; // Cek apakah user adalah admin

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $labels[] = $date->format('D');

            $query = Mail::whereDate('created_at', $date)
                ->where('status', 'Submitted');
                // dd($query->value('id'));

                // dd($user->id);
            if ($groupID !== 'admin') {
                $query->where('group_id', $groupID);
            }

            $data[] = $query->count();
                // dd($query->count());

        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Mails Created',
                    'data' => $data,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Bisa diubah ke 'line' jika ingin grafik garis
    }
}
