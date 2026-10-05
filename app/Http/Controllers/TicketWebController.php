<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketStatusRequest;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class TicketWebController extends Controller
{
    public function __construct(
        protected TicketService $ticketService
    ) {}

    /**
     * Display dashboard with ticket listing and active filters.
     */
    public function index(Request $request): View
    {
        // dd($request->only(['search', 'priority', 'status', 'is_overdue']));
        // dd(Ticket::all(), Ticket::query()->latest('id')->paginate(10));
        $tickets = Ticket::query()
            ->filter($request->only(['search', 'priority', 'status', 'is_overdue']))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('tickets.index', compact('tickets'));
    }

    /**
     * Handle creation of a new ticket from web form.
     */
    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $this->ticketService->createTicket($request->validated());

        return redirect()->route('tickets.web.index')->with('success', 'Ticket created successfully.');
    }

    /**
     * Update the ticket status directly from the UI dropdown.
     */
    public function updateStatus(UpdateTicketStatusRequest $request, Ticket $ticket): RedirectResponse
    {
        // چک کردن در کنترلر قبل از صدا زدن سرویس
        if ($ticket->status === 'done') {
            //   return back()->with('success', 'Ticket status updated successfully.');
            // به جای with('success', ...) می‌نویسید:
            return back()->with('error', 'تیکت‌های بسته‌شده (Done) دیگر قابل تغییر وضعیت نیستند.');

            return back()->withErrors([
                'status' => 'تیکت‌های بسته‌شده (Done) دیگر قابل تغییر وضعیت نیستند.',
            ]);
        }
        $this->ticketService->updateStatus($ticket, $request->validated('status'));

        return back()->with('success', 'Ticket status updated successfully.');
    }

    public function updatePriority(Request $request, Ticket $ticket)
    {
        // ۱. اعتبارسنجی ورودی
        $request->validate([
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
        ]);

        // ۲. ارسال به سرویس
        $this->ticketService->updatePriority($ticket, $request->input('priority'));

        // ۳. ریدایرکت با پیام موفقیت
        return back()->with('success', "اولویت تیکت #{$ticket->id} به روز شد.");
    }
}
