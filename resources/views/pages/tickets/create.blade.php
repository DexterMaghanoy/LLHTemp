@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Create Ticket" />

    <div class="space-y-6">
        <x-common.component-card title="Create Ticket">
            <form method="POST" action="{{ route('tickets.store') }}" class="grid grid-cols-1 gap-6 md:grid-cols-2">
                @csrf

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Subject<span class="text-error-500">*</span></label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    @error('subject')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Description<span class="text-error-500">*</span></label>
                    <textarea name="description" rows="4" required class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Category</label>
                    <input type="text" name="category" value="{{ old('category') }}" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Priority<span class="text-error-500">*</span></label>
                    <select name="priority" required class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">Select priority</option>
                        @foreach (\App\Models\Ticket::priorities() as $priority)
                            <option value="{{ $priority }}" @selected(old('priority') === $priority)>{{ ucfirst($priority) }}</option>
                        @endforeach
                    </select>
                    @error('priority')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Department<span class="text-error-500">*</span></label>
                    <select name="department_id" required class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">Select department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2 flex items-end gap-3">
                    <button type="submit" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">Create Ticket</button>
                    <a href="{{ route('tickets.index') }}" class="border border-gray-300 dark:border-gray-800 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">Cancel</a>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection


