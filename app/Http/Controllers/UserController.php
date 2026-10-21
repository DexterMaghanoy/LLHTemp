<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('department');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('pages.users.index', compact('users'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();

        return view('pages.users.form', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::min(8), 'confirmed'],
            'role' => ['required', 'in:admin,staff,guest'],
            'department_id' => ['nullable', 'exists:departments,id', 'required_if:role,staff'],
        ]);

        User::create([
            'name' => trim($data['name']),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'department_id' => $data['department_id'] ?? null,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $departments = Department::orderBy('name')->get();

        return view('pages.users.form', compact('user', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', Password::min(8), 'confirmed'],
            'role' => ['required', 'in:admin,staff,guest'],
            'department_id' => ['nullable', 'exists:departments,id', 'required_if:role,staff'],
        ]);

        $user->name = trim($data['name']);
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->department_id = $data['role'] === 'staff' ? ($data['department_id'] ?? null) : null;

        if ($request->filled('password')) {
            $user->password = Hash::make($data['password']);
        }

        if ($user->id === auth()->id() && $data['role'] !== 'admin') {
            return back()->withErrors(['role' => 'You cannot remove your own admin role.'])->withInput();
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'At least one admin account must remain.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted.');
    }
}
