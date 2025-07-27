<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;

class AdminStatsService
{
    private const CACHE_TTL = 3600; // 1 heure

    public function getUserStats(): array
    {
        return Cache::remember('admin_stats:users', self::CACHE_TTL, function () {
            return [
                'total' => User::count(),
                'new_today' => User::whereDate('created_at', today())->count(),
                'by_type' => User::groupBy('type')
                              ->selectRaw('type, count(*) as count')
                              ->pluck('count', 'type')
            ];
        });
    }

    public function getTransactionStats(): array
    {
        return Cache::remember('admin_stats:transactions', self::CACHE_TTL, function () {
            return [
                'total' => Transaction::count(),
                'revenue_today' => Transaction::whereDate('created_at', today())
                                           ->sum('amount'),
                'pending' => Transaction::where('status', 'pending')->count()
            ];
        });
    }

    public function getSystemStats(): array
    {
        // Statistiques système avancées
        return [
            'queue_jobs' => app('queue.connection')->size(),
            'storage' => [
                'used' => disk_total_space(storage_path()) - disk_free_space(storage_path()),
                'total' => disk_total_space(storage_path())
            ]
        ];
    }
}