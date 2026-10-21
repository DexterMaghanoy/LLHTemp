@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Ticket {{ $ticket->ticket_number }}" />

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2 space-y-6">
            <x-common.component-card title="{{ $ticket->subject }}" desc="{{ $ticket->ticket_number }}">
                <div class="space-y-4">
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full px-2 py-0.5 text-theme-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                        <span class="rounded-full px-2 py-0.5 text-theme-xs font-medium bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-orange-400">{{ ucfirst($ticket->priority) }}</span>
                        @if ($ticket->category)
                            <span class="rounded-full px-2 py-0.5 text-theme-xs font-medium bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-400">{{ $ticket->category }}</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $ticket->description }}</p>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Raised By</p>
                            <p class="font-medium text-gray-800 dark:text-white/90">{{ $ticket->requester->name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Assigned To</p>
                            <p class="font-medium text-gray-800 dark:text-white/90">{{ $ticket->assignee->name ?? 'Unassigned' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Handling Department</p>
                            <p class="font-medium text-gray-800 dark:text-white/90">{{ $ticket->department->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Created</p>
                            <p class="font-medium text-gray-800 dark:text-white/90">{{ $ticket->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </x-common.component-card>

            <x-common.component-card title="Conversation" desc="Comments and internal notes.">
                <div class="space-y-4">
                    @forelse ($ticket->comments as $comment)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $comment->user->name }}</p>
                                <div class="flex items-center gap-2">
                                    @if ($comment->is_internal)
                                        <span class="rounded-full px-2 py-0.5 text-theme-xs font-medium bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-orange-400">Internal</span>
                                    @endif
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $comment->body }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">No comments yet.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('tickets.comments.store', $ticket) }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Add comment</label>
                        <textarea name="body" rows="3" required
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"></textarea>
                    </div>
                    @if (auth()->user()->isStaff())
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-400">
                            <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                            Internal note (staff only)
                        </label>
                    @endif
                    <button type="submit" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">Post Comment</button>
                </form>
            </x-common.component-card>
        </div>
        <div class="space-y-6">
            @if (auth()->user()->isStaff())
                <x-common.component-card title="Manage" desc="Update status, priority and assignment.">
                    <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
                            <select name="status" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                @foreach (\App\Models\Ticket::statuses() as $status)
                                    <option value="{{ $status }}" @selected($ticket->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Priority</label>
                            <select name="priority" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                @foreach (\App\Models\Ticket::priorities() as $priority)
                                    <option value="{{ $priority }}" @selected($ticket->priority === $priority)>{{ ucfirst($priority) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Assignee</label>
                            <select name="assignee_id" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Unassigned</option>
                                @foreach ($staff as $member)
                                    <option value="{{ $member->id }}" @selected($ticket->assignee_id === $member->id)>{{ $member->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Department<span class="text-error-500">*</span></label>
                            <select name="department_id" required class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="" disabled>Select department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" @selected($ticket->department_id === $department->id)>{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Subject</label>
                            <input type="text" name="subject" value="{{ $ticket->subject }}" required class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Description</label>
                            <textarea name="description" rows="4" required class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ $ticket->description }}</textarea>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Category</label>
                            <input type="text" name="category" value="{{ $ticket->category }}" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>
                        <button type="submit" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">Save Changes</button>
                    </form>

                    <form method="POST" action="{{ route('tickets.destroy', $ticket) }}" class="mt-4" onsubmit="return confirm('Delete this ticket?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg border border-error-300 bg-error-50 px-4 py-2.5 text-sm font-medium text-error-600 hover:bg-error-100 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">Delete Ticket</button>
                    </form>
                </x-common.component-card>
            @endif
        </div>
    </div>
@endsection

