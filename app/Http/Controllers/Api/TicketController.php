<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketStatusRequest;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(
        protected TicketService $ticketService
    ) {}

    /**
     * Display a listing of tickets with search, filtering, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $tickets = Ticket::query()
            ->filter($request->only(['search', 'priority', 'status', 'is_overdue']))
            ->latest('id')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $tickets,
        ]);
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(StoreTicketRequest $request): JsonResponse
    {
        $ticket = $this->ticketService->createTicket($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Ticket created successfully.',
            'data' => $ticket,
        ], 201);
    }

    /**
     * Display the specified ticket with its status transition history.
     */
    public function show(Ticket $ticket): JsonResponse
    {
        $ticket->load(['histories' => fn($query) => $query->latest('changed_at')]);

        return response()->json([
            'success' => true,
            'data' => $ticket,
        ]);
    }

    /**
     * Update the status of the specified ticket.
     */
    public function updateStatus(UpdateTicketStatusRequest $request, Ticket $ticket): JsonResponse
    {
        $updatedTicket = $this->ticketService->updateStatus($ticket, $request->validated('status'));

        return response()->json([
            'success' => true,
            'message' => 'Ticket status updated successfully.',
            'data' => $updatedTicket,
        ]);
    }
}
