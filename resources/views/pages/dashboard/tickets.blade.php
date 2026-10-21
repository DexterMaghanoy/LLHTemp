@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Dashboard" />

    <div class="mb-4 flex flex-wrap items-center gap-2">
    @php
        $period = $period ?? 'daily';
        $rangeStart = $rangeStart ?? now()->startOfDay();
        $rangeEnd = $rangeEnd ?? now()->endOfDay();
    @endphp
        @foreach (['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $key => $label)
            <a href="{{ route('dashboard', ['period' => $key]) }}"
               class="rounded-lg px-4 py-2 text-sm font-medium transition {{ $period === $key ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
    <p class="mb-4 text-xs italic text-gray-500 dark:text-gray-400">
        {{ $rangeStart->format('M d, Y') }} - {{ $rangeEnd->format('M d, Y') }}
    </p>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        @php
            $ticketQuery = \App\Models\Ticket::query();
            if (!auth()->user()->isAdmin()) {
                $ticketQuery->where(function ($q) {
                    $q->where('requester_id', auth()->id())
                        ->orWhere('assignee_id', auth()->id());
                });
            }
            $ticketQuery->whereBetween('created_at', [$rangeStart, $rangeEnd]);

            $stats = [
                ['label' => 'Total Tickets', 'value' => (clone $ticketQuery)->count(), 'color' => 'bg-brand-500'],
                ['label' => 'Open', 'value' => (clone $ticketQuery)->where('status', 'open')->count(), 'color' => 'bg-warning-500'],
                ['label' => 'In Progress', 'value' => (clone $ticketQuery)->where('status', 'in_progress')->count(), 'color' => 'bg-blue-light-500'],
                ['label' => 'Resolved', 'value' => (clone $ticketQuery)->where('status', 'resolved')->count(), 'color' => 'bg-success-500'],
            ];
        @endphp
        @foreach ($stats as $stat)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</p>
                        <h3 class="mt-1 text-title-sm font-semibold text-gray-800 dark:text-white/90">{{ $stat['value'] }}</h3>
                    </div>
                    <span class="inline-block size-3 rounded-full {{ $stat['color'] }}"></span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        <x-common.component-card title="Recent Tickets" desc="Latest support requests.">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Ticket</p></th>
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Subject</p></th>
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p></th>
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Priority</p></th>
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Created</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ((clone $ticketQuery)->with('requester')->latest()->take(8)->get() as $ticket)
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="py-3 whitespace-nowrap"><p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $ticket->ticket_number }}</p></td>
                                <td class="py-3"><a href="{{ route('tickets.show', $ticket) }}" class="text-gray-800 text-theme-sm dark:text-white/90 hover:text-brand-500">{{ $ticket->subject }}</a></td>
                                <td class="py-3 whitespace-nowrap"><span class="rounded-full px-2 py-0.5 text-theme-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></td>
                                <td class="py-3 whitespace-nowrap"><span class="rounded-full px-2 py-0.5 text-theme-xs font-medium bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-orange-400">{{ ucfirst($ticket->priority) }}</span></td>
                                <td class="py-3 whitespace-nowrap"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $ticket->created_at->diffForHumans() }}</p></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-common.component-card>
    </div>
@endsection