<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class TicketService
{
    /**
     * Create a new ticket and record the initial status history.
     */
    public function createTicket(array $data): Ticket
    {
        return DB::transaction(function () use ($data) {
            $now = now();

            $ticket = Ticket::create([
                ...$data,
                'status' => 'open',
                'last_opened_at' => $now,
                'total_open_seconds' => 0,
            ]);

            $ticket->histories()->create([
                'from_status' => null,
                'to_status' => 'open',
                'changed_at' => $now,
            ]);

            return $ticket;
        });
    }

    /**
     * Update the status of an existing ticket and manage open duration tracking.
     */
    public function updateStatus(Ticket $ticket, string $newStatus)
    {
        // Avoid redundant state transitions
        if ($ticket->status === $newStatus) {
            return $ticket;
        }
// dd($ticket->status);
        // اگر تیکت قبلاً Done بوده، جلویش را می‌گیریم
        // if ($ticket->status === 'done') {
        //     return back()->withErrors(['status' => 'تیکت بسته‌شده قابل ویرایش نیست.']);
        // }



        // // 👈 قانون بیزینسی: تیکت Done نباید تغییر وضعیت پیدا کند
        // if ($ticket->status === 'done') {
        //     if ($ticket->status === 'done') {
        //         throw ValidationException::withMessages([
        //             'status' => 'امکان تغییر وضعیت تیکت بسته شده وجود ندارد.',
        //         ]);
        //     }
        // }

        return DB::transaction(function () use ($ticket, $newStatus) {
            $now = now();
            $oldStatus = $ticket->status;

            // If moving out of 'open' status, accrue elapsed open time
            if ($oldStatus === 'open' && $ticket->last_opened_at) {
                $ticket->total_open_seconds += (int) $ticket->last_opened_at->diffInSeconds($now);
                $ticket->last_opened_at = null;
            }

            // If entering 'open' status, initiate a new timer anchor
            if ($newStatus === 'open') {
                $ticket->last_opened_at = $now;
            }

            $ticket->status = $newStatus;
            $ticket->save();

            // Record status change event
            $ticket->histories()->create([
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_at' => $now,
            ]);

            return $ticket;
        });
    }

    public function updatePriority(Ticket $ticket, string $newPriority): Ticket
    {
        // قانون بیزینسی ۱: تیکتی که بسته شده (Done) اولویتش نباید تغییر کند
        if ($ticket->status === 'done') {
            throw ValidationException::withMessages([
                'priority' => 'امکان تغییر اولویت تیکت بسته شده وجود ندارد.',
            ]);
        }

        $ticket->priority = $newPriority;
        $ticket->save();

        return $ticket;
    }
}
