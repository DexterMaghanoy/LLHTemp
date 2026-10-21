@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Users" />

    <div class="space-y-6">
        <x-common.component-card title="All Users" desc="Manage hotel staff, department users, and guest accounts.">
            <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                <select name="role"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">All roles</option>
                    @foreach (['admin' => 'Admin', 'staff' => 'Department', 'guest' => 'Guest'] as $role => $label)
                        <option value="{{ $role }}" @selected(request('role') === $role)>{{ $label }}</option>
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
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Name</p></th>
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Email</p></th>
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Role</p></th>
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Department</p></th>
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Created</p></th>
                            <th class="py-3 text-start"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Action</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="py-3 whitespace-nowrap"><p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $user->name }}</p></td>
                                <td class="py-3 whitespace-nowrap"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $user->email }}</p></td>
                                <td class="py-3 whitespace-nowrap"><span class="rounded-full px-2 py-0.5 text-theme-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $user->role === 'staff' ? 'Department' : ucfirst($user->role) }}</span></td>
                                <td class="py-3 whitespace-nowrap"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $user->department->name ?? 'None' }}</p></td>
                                <td class="py-3 whitespace-nowrap"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $user->created_at->diffForHumans() }}</p></td>
                                <td class="py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('users.edit', $user) }}" class="text-brand-500 hover:text-brand-600 text-theme-sm font-medium">Edit</a>
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-error-500 hover:text-error-600 text-theme-sm font-medium">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 text-center text-gray-500 text-theme-sm dark:text-gray-400">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $users->links() }}</div>
        </x-common.component-card>

        <div class="flex justify-end">
            <a href="{{ route('users.create') }}" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">New User</a>
        </div>
    </div>
@endsection
