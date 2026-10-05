<td class="py-3 px-4">
    <div class="inline-block">
        <form action="{{ route('tickets.web.update-priority', $ticket) }}" method="POST">
            @csrf
            @method('PATCH')

            @php
                $priorityClasses = [
                    'low'    => 'bg-gray-100 text-gray-700 border-gray-300',
                    'medium' => 'bg-amber-100 text-amber-800 border-amber-300',
                    'high'   => 'bg-rose-100 text-rose-800 border-rose-300 font-semibold',
                ];
            @endphp

            <select name="priority" 
                    onchange="this.form.submit()" 
                    class="text-xs border rounded px-2 py-1 cursor-pointer focus:outline-blue-500 {{ $priorityClasses[$ticket->priority] ?? 'bg-slate-100' }}"
                    {{ $ticket->status === 'done' ? 'disabled' : '' }}>
                
                <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>High</option>
            </select>
        </form>

        {{-- نمایش ارور اگر اعتبارسنجی رد شد --}}
        @error('priority')
            <div class="text-[11px] text-rose-600 font-medium mt-1">
                {{ $message }}
            </div>
        @enderror
    </div>
</td>
