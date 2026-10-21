@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Tickets" />

    <div class="space-y-6">
        <x-common.component-card title="All Tickets" desc="Manage hotel support tickets.">
            <form method="GET" action="{{ route('tickets.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-5">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search subject or number"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                <select name="status"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\Ticket::statuses() as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <select name="priority"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">All priorities</option>
                    @foreach (\App\Models\Ticket::priorities() as $priority)
                        <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
                <select name="department_id"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">All departments</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
                <button type="submit"
                    class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex h-11 items-center justify-center rounded-lg px-4 text-sm font-medium text-white transition">
                    Filter
                </button>
            </form>

            <div class="mt-6 max-w-full overflow-x-auto custom-scrollbar">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Ticket</p></th>
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Subject</p></th>
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p></th>
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Priority</p></th>
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Raised By</p></th>
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Assigned To</p></th>
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Created</p></th>
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Action</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $ticket)
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="py-3 whitespace-nowrap"><p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $ticket->ticket_number }}</p></td>
                                <td class="py-3"><a href="{{ route('tickets.show', $ticket) }}" class="text-gray-800 text-theme-sm dark:text-white/90 hover:text-brand-500">{{ $ticket->subject }}</a></td>
                                <td class="py-3 whitespace-nowrap"><span class="rounded-full px-2 py-0.5 text-theme-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></td>
                                <td class="py-3 whitespace-nowrap"><span class="rounded-full px-2 py-0.5 text-theme-xs font-medium bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-orange-400">{{ ucfirst($ticket->priority) }}</span></td>
                                <td class="py-3 whitespace-nowrap"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $ticket->requester->name }}</p></td>
                                <td class="py-3 whitespace-nowrap"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $ticket->assignee->name ?? 'Unassigned' }}</p></td>
                                <td class="py-3 whitespace-nowrap"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $ticket->created_at->diffForHumans() }}</p></td>
                                <td class="py-3 whitespace-nowrap"><a href="{{ route('tickets.show', $ticket) }}" class="text-brand-500 hover:text-brand-600 text-theme-sm font-medium">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="py-6 text-center text-gray-500 text-theme-sm dark:text-gray-400">No tickets found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $tickets->links() }}</div>
        </x-common.component-card>

        <div class="flex justify-end">
            <a href="{{ route('tickets.create') }}" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">New Ticket</a>
        </div>
    </div>
@endsection

