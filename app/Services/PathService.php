<?php
namespace App\Services;

use App\Models\Group;
use App\Models\PathDetail;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class PathService
{
    public function findShortestPath($senderId, $targetId)
{
    // Ambil grup dari database
    $senderGroup = Group::find($senderId);
    $targetGroup = Group::find($targetId);

    if (!$senderGroup || !$targetGroup) {
        Notification::make()
            ->title('Gagal')
            ->body('Salah satu grup tidak ditemukan.')
            ->danger()
            ->send();

        throw ValidationException::withMessages([
            'group' => 'Salah satu grup tidak ditemukan.',
        ]);
    }

    // Cek hubungan langsung: apakah sender adalah ancestor atau descendant?
    if ($this->isAncestor($senderGroup, $targetGroup)) {
        return $this->findPathDownward($senderGroup, $targetGroup);
    } elseif ($this->isAncestor($targetGroup, $senderGroup)) {
        return $this->findPathUpward($senderGroup, $targetGroup);
    }

    // Tidak ada hubungan langsung
    Notification::make()
        ->title('Gagal')
        ->body("Tidak ada jalur antara {$senderGroup->name} dan {$targetGroup->name}.")
        ->danger()
        ->send();

    throw ValidationException::withMessages([
        'group' => "Tidak ada jalur antara {$senderGroup->name} dan {$targetGroup->name}.",
    ]);
}

/**
 * Cek apakah $ancestor adalah nenek moyang (ancestor) dari $descendant
 */
private function isAncestor($ancestor, $descendant)
{
    while ($descendant->parent_id) {
        if ($descendant->parent_id == $ancestor->id) {
            return true;
        }
        $descendant = Group::find($descendant->parent_id);
    }
    return false;
}

/**
 * Cari jalur dari ancestor ke descendant (turun)
 */
private function findPathDownward($ancestor, $descendant)
{
    $queue = [[$ancestor->id]];
    
    while (!empty($queue)) {
        $path = array_shift($queue);
        $currentId = end($path);

        if ($currentId == $descendant->id) {
            return $path;
        }

        // Ambil child dan lanjutkan pencarian
        $children = Group::where('parent_id', $currentId)->get();
        foreach ($children as $child) {
            $newPath = $path;
            $newPath[] = $child->id;
            $queue[] = $newPath;
        }
    }

    return null;
}

/**
 * Cari jalur dari descendant ke ancestor (naik)
 */
private function findPathUpward($descendant, $ancestor)
{
    $path = [$descendant->id];

    while ($descendant->parent_id) {
        $descendant = Group::find($descendant->parent_id);
        $path[] = $descendant->id;

        if ($descendant->id == $ancestor->id) {
            return $path;
        }
    }

    return null;
}
    

    /**
     * Membuat PathDetail berdasarkan jalur terpendek
     */
    public function createPathDetail($data)
    {
        

        // Cari jalur terpendek
        $shortestPath = $this->findShortestPath($data['sender_id'], $data['receiver_id']);

        if ($shortestPath) {
            foreach ($shortestPath as $index => $groupId) {
                PathDetail::create([
                    'sender_id' => $data['sender_id'],
                    'receiver_id' => $data['receiver_id'],
                    'group_id' => $groupId,
                    'path_id' => $data['path_id'],
                    'order' => $index + 1,
                ]);
            }
        } else {
            Log::error("Grup tidak terhubung. Gagal membuat PathDetail.");
        }
    }
}
