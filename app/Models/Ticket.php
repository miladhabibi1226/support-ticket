<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    // Overdue threshold: 24 hours in seconds
    public const OVERDUE_THRESHOLD_SECONDS = 86400;

    protected $fillable = ['title', 'requester_name', 'description', 'priority', 'status', 'total_open_seconds', 'last_opened_at',];

    protected $casts = ['last_opened_at' => 'datetime', 'total_open_seconds' => 'integer',];

    // Append custom computed fields to API responses
    protected $appends = ['is_overdue', 'open_duration_in_seconds',];

    public function histories(): HasMany
    {
        return $this->hasMany(TicketStatusHistory::class);
    }

    /**
     * Calculate total seconds the ticket has been in 'open' status so far.
     */
    public function getOpenDurationInSecondsAttribute(): int
    {
        $duration = (int) $this->total_open_seconds;

        if ($this->status === 'open' && $this->last_opened_at) {
            $duration += (int) $this->last_opened_at->diffInSeconds(now());
        }

        return $duration;
    }


    /**
     * Determine whether the ticket is overdue based on SLA rules:
     * - Priority must be 'high'
     * - Status must not be 'done' (یا اگر فقط برای open می‌خواهید: status must be 'open')
     * - Total open time must exceed 24 hours (86,400 seconds)
     */
    // public function getIsOverdueAttribute(): bool
    // {
    //     // حالت ۱: اگر می‌خواهید همه تیکت‌های غیر Done (شامل In Progress) برچسب بخورند:
    //     if ($this->priority !== 'high' || $this->status === 'done') {
    //         return false;
    //     }

    //     /* 
    //     // حالت ۲: اگر می‌خواهید فقط تیکت‌های Open برچسب بخورند (این دو خط را از کامنت خارج کنید و شرط بالا را حذف کنید):
    //     if ($this->priority !== 'high' || $this->status !== 'open') {
    //         return false;
    //     }
    //     */

    //     return $this->open_duration_in_seconds > self::OVERDUE_THRESHOLD_SECONDS;
    // }

    public function getIsOverdueAttribute(): bool
    {

        if (
            in_array($this->priority, ['high', 'medium'], true) && $this->status !== 'done' &&
            $this->open_duration_in_seconds > self::OVERDUE_THRESHOLD_SECONDS
        ) {
            return true;
        }



        // فقط تیکت‌های با اولویت بالا و در وضعیت Open برچسب می‌خورند
        // if ($this->priority !== 'high' || $this->status !== 'open') {
        if ($this->priority !== 'high') {
            return false;
        }

        return $this->open_duration_in_seconds > self::OVERDUE_THRESHOLD_SECONDS;
    }


    // public function getIsOverdueAttribute(): bool
    // {
    //     if ($this->priority !== 'high' || $this->status === 'done') {
    //         return false;
    //     }

    //     return $this->open_duration_in_seconds > self::OVERDUE_THRESHOLD_SECONDS;
    // }

    /**
     * Scope query to handle search and filtering cleanly.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        // dd( $filters);
        return $query
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('requester_name', 'like', "%{$search}%");
                        // ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, function (Builder $q, string $status) {
                $q->where('status', $status);
            })
            ->when($filters['priority'] ?? null, function (Builder $q, string $priority) {
                $q->where('priority', $priority);
            })
            ->when(isset($filters['is_overdue']), function (Builder $q) use ($filters) {
                $isOverdue = filter_var($filters['is_overdue'], FILTER_VALIDATE_BOOLEAN);
                $threshold = self::OVERDUE_THRESHOLD_SECONDS;

                $q->where('priority', 'high')->where('status', '!=', 'done');

                $rawDurationSql = "
                    (CASE 
                        WHEN status = 'open' AND last_opened_at IS NOT NULL 
                            THEN total_open_seconds + TIMESTAMPDIFF(SECOND, last_opened_at, NOW())
                        ELSE total_open_seconds 
                    END)
                ";

                if ($isOverdue) {
                    $q->whereRaw("{$rawDurationSql} > ?", [$threshold]);
                } else {
                    $q->whereRaw("{$rawDurationSql} <= ?", [$threshold]);
                }
            });
    }
}
