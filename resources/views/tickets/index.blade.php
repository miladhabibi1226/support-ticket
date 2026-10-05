<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Ticket System</title>
    <!-- Tailwind CSS CDN for quick and clean UI -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 min-h-screen text-slate-800 p-4 sm:p-8">

    <div class="max-w-6xl mx-auto space-y-6">

        <!-- Header -->
        <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Support Ticket Management</h1>
                <p class="text-sm text-slate-500">Track, prioritize, and manage customer support requests</p>
            </div>
        </header>

        <!-- Flash Message -->
        @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
        @endif

        <!-- Create Ticket Card -->
        <section class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Create New Ticket</h2>
            <form action="{{ route('tickets.web.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Title *</label>
                        <input type="text" name="title" value="{{ old('title') }}" required class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-blue-500 @error('title') border-red-500 @enderror">
                        @error('title') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Requester Name *</label>
                        <input type="text" name="requester_name" value="{{ old('requester_name') }}" required class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-blue-500 @error('requester_name') border-red-500 @enderror">
                        @error('requester_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Priority *</label>
                        <select name="priority" required class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-blue-500">
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-blue-500">{{ old('description') }}</textarea>
                </div>
                <div class="text-right">
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg transition">Submit Ticket</button>
                </div>
            </form>
        </section>

        <!-- Filters Section -->
        <section class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
            <form method="GET" action="{{ route('tickets.web.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Search (Title / Requester)</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or title..." class="w-full px-3 py-1.5 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-1.5 border rounded-lg text-sm">
                        <option value="">All Statuses</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Priority</label>
                    <select name="priority" class="w-full px-3 py-1.5 border rounded-lg text-sm">
                        <option value="">All Priorities</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="inline-flex items-center cursor-pointer mt-5">
                        <input type="checkbox" name="is_overdue" value="1" {{ request('is_overdue') ? 'checked' : '' }} class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                        <span class="ml-2 text-xs font-semibold text-red-600">Overdue SLA</span>
                    </label>
                    <button type="submit" class="ml-auto px-4 py-1.5 bg-slate-800 text-white rounded-lg text-sm hover:bg-slate-900 transition">Filter</button>
                    @if(request()->hasAny(['search', 'status', 'priority', 'is_overdue']))
                    <a href="{{ route('tickets.web.index') }}" class="px-2 py-1.5 text-xs text-slate-500 hover:underline">Reset</a>
                    @endif
                </div>
            </form>
        </section>
        @if (session('error'))
        <div class="p-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
            {{ session('error') }}
        </div>
        @endif

        <!-- Tickets Table -->
        <section class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 border-collapse">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b">
                        <tr>
                            <th class="py-3 px-4">#ID</th>
                            <th class="py-3 px-4">Title & Requester</th>
                            <th class="py-3 px-4">Priority</th>
                            <th class="py-3 px-4">Status & Change</th>
                            <th class="py-3 px-4">Open Duration</th>
                            <th class="py-3 px-4">Created At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($tickets as $ticket)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-mono text-xs text-slate-400">#{{ $ticket->id }}</td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-900">{{ $ticket->title }}</div>
                                <div class="text-xs text-slate-500">By: {{ $ticket->requester_name }}</div>
                                @if($ticket->description)
                                <p class="text-xs text-slate-400 mt-1 line-clamp-1">{{ $ticket->description }}</p>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @php
                                $priorityColors = [
                                'low' => 'bg-gray-100 text-gray-700',
                                'medium' => 'bg-amber-100 text-amber-700',
                                'high' => 'bg-rose-100 text-rose-700 font-semibold',
                                ];
                                @endphp
                                <span class="inline-block px-2 py-0.5 rounded text-xs capitalize {{ $priorityColors[$ticket->priority] ?? 'bg-slate-100' }}">
                                    {{ $ticket->priority }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <form action="{{ route('tickets.web.update-status', $ticket) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="this.form.submit()" class="text-xs border rounded px-2 py-1 font-medium bg-white focus:outline-blue-500">
                                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="done" {{ $ticket->status === 'done' ? 'selected' : '' }}>Done</option>
                                        </select>
                                    </form>

                                    @if($ticket->is_overdue)
                                    <span class="px-2 py-0.5 bg-red-600 text-white rounded text-xs font-bold animate-pulse">OVERDUE</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4 font-mono text-xs">
                                <!-- {{ gmdate('H:i:s', $ticket->open_duration_in_seconds) }} -->
                                {{ \Carbon\CarbonInterval::seconds($ticket->open_duration_in_seconds)->cascade()->forHumans(['short' => true]) }}

                            </td>
                            <td class="py-3 px-4 text-xs text-slate-400">
                                {{ $ticket->created_at->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                No tickets found matching current criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-slate-100">
                {{ $tickets->links() }}
            </div>
        </section>

    </div>

</body>

</html>