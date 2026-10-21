@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="{
        isCreateTicketModal: false,
        submitting: false,
        ticketForm: { subject: '', description: '', priority: '', department_id: '', category: '' },
        formErrors: {},
        resetForm() {
            this.ticketForm = { subject: '', description: '', priority: '', department_id: '', category: '' };
            this.formErrors = {};
            this.submitting = false;
        },
        async createTicket() {
            this.submitting = true;
            this.formErrors = {};
            try {
                await window.axios.post('{{ route('tickets.store') }}', {
                    subject: this.ticketForm.subject,
                    description: this.ticketForm.description,
                    priority: this.ticketForm.priority,
                    category: this.ticketForm.category,
                    department_id: this.ticketForm.department_id,
                });
                this.isCreateTicketModal = false;
                this.resetForm();
                window.location.reload();
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors || {};
                    Object.keys(errors).forEach(key => {
                        this.formErrors[key] = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
                    });
                } else {
                    alert('Failed to create ticket. Please try again.');
                }
            } finally {
                this.submitting = false;
            }
        }
    }">
        <x-common.page-breadcrumb pageTitle="Dashboard" />

    <div class="mb-4 flex flex-wrap items-center gap-2">
    @php
        $period = $period ?? 'daily';
        $rangeStart = $rangeStart ?? now()->startOfDay();
        $rangeEnd = $rangeEnd ?? now()->endOfDay();
        $departments = $departments ?? \App\Models\Department::orderBy('name')->get();
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
        <x-common.component-card title="Recent Tickets" desc="Latest support requests." actionButton='<button @click="isCreateTicketModal = true" type="button" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">+ New Ticket</button>'>
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

        <!-- Create Ticket Modal -->
        <div x-show="isCreateTicketModal" x-cloak
             @click.away="isCreateTicketModal = false"
             @keydown.escape.window="isCreateTicketModal = false"
             class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-4 md:p-6">
            <div class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-sm"
                 @click="isCreateTicketModal = false"></div>

            <div class="relative w-full max-w-lg bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-lg m-4 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Create Ticket</h3>
                    <button @click="isCreateTicketModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="createTicket" class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Subject<span class="text-error-500">*</span></label>
                        <input type="text" name="subject" x-model="ticketForm.subject" required
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        <span x-show="formErrors.subject" x-text="formErrors.subject" class="mt-1 text-sm text-error-500"></span>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Description<span class="text-error-500">*</span></label>
                        <textarea name="description" rows="4" x-model="ticketForm.description" required
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                        <span x-show="formErrors.description" x-text="formErrors.description" class="mt-1 text-sm text-error-500"></span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Priority<span class="text-error-500">*</span></label>
                            <select name="priority" x-model="ticketForm.priority" required
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Select priority</option>
                                @foreach (\App\Models\Ticket::priorities() as $priority)
                                    <option value="{{ $priority }}">{{ ucfirst($priority) }}</option>
                                @endforeach
                            </select>
                            <span x-show="formErrors.priority" x-text="formErrors.priority" class="mt-1 text-sm text-error-500"></span>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Department<span class="text-error-500">*</span></label>
                            <select name="department_id" x-model="ticketForm.department_id" required
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Select department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            <span x-show="formErrors.department_id" x-text="formErrors.department_id" class="mt-1 text-sm text-error-500"></span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Category</label>
                        <input type="text" name="category" x-model="ticketForm.category"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        <span x-show="formErrors.category" x-text="formErrors.category" class="mt-1 text-sm text-error-500"></span>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <button type="button" @click="isCreateTicketModal = false; resetForm()"
                            class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">Cancel</button>
                        <button type="submit" :disabled="submitting"
                            class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 disabled:opacity-70 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">
                            <span x-show="!submitting">Create Ticket</span>
                            <span x-show="submitting" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                </svg>
                                Creating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (token) {
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
            }
        </script>

    </div>
@endsection