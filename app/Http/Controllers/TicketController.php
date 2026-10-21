<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['requester', 'assignee', 'department']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }

        if (!auth()->user()->isAdmin()) {
            $query->where(function ($q) {
                $q->where('requester_id', auth()->id())
                    ->orWhere('assignee_id', auth()->id());
            });
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();

        $departments = Department::orderBy('name')->get();
        $staff = User::whereIn('role', ['admin', 'staff'])->orderBy('name')->get();

        return view('pages.tickets.index', compact('tickets', 'departments', 'staff'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('pages.tickets.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'category' => ['nullable', 'string', 'max:100'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]);

        $ticket = Ticket::create([
            'ticket_number' => 'TKT-'.strtoupper(Str::random(8)),
            'subject' => $data['subject'],
            'description' => $data['description'],
            'priority' => $data['priority'],
            'category' => $data['category'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'requester_id' => auth()->id(),
            'status' => 'open',
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket)
    {
        $this->authorizeView($ticket);
        $ticket->load(['requester', 'assignee', 'department', 'comments.user', 'attachments.uploader']);
        $departments = Department::orderBy('name')->get();
        $staff = User::whereIn('role', ['admin', 'staff'])->orderBy('name')->get();

        return view('pages.tickets.show', compact('ticket', 'departments', 'staff'));
    }

    public function edit(Ticket $ticket)
    {
        $this->authorizeManage($ticket);
        $departments = Department::orderBy('name')->get();
        $staff = User::whereIn('role', ['admin', 'staff'])->orderBy('name')->get();

        return view('pages.tickets.edit', compact('ticket', 'departments', 'staff'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorizeManage($ticket);

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'category' => ['nullable', 'string', 'max:100'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:open,in_progress,pending,resolved,closed'],
        ]);

        if ($data['status'] === 'resolved' && $ticket->resolved_at === null) {
            $data['resolved_at'] = now();
        }
        if ($data['status'] === 'closed' && $ticket->closed_at === null) {
            $data['closed_at'] = now();
        }

        $ticket->update($data);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket updated successfully.');
    }

    public function destroy(Ticket $ticket)
    {
        $this->authorizeManage($ticket);
        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Ticket deleted.');
    }

    public function storeComment(Request $request, Ticket $ticket)
    {
        $this->authorizeView($ticket);

        $data = $request->validate([
            'body' => ['required', 'string'],
            'is_internal' => ['nullable', 'boolean'],
        ]);

        $isInternal = (bool) ($data['is_internal'] ?? false);
        if ($isInternal && !auth()->user()->isStaff()) {
            abort(403);
        }

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'body' => $data['body'],
            'is_internal' => $isInternal,
        ]);

        return back()->with('success', 'Comment added.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $this->authorizeManage($ticket);

        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,pending,resolved,closed'],
        ]);

        if ($data['status'] === 'resolved' && $ticket->resolved_at === null) {
            $ticket->resolved_at = now();
        }
        if ($data['status'] === 'closed' && $ticket->closed_at === null) {
            $ticket->closed_at = now();
        }

        $ticket->status = $data['status'];
        $ticket->save();

        return back()->with('success', 'Status updated.');
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $this->authorizeManage($ticket);

        $data = $request->validate([
            'assignee_id' => ['nullable', 'exists:users,id'],
        ]);

        $ticket->assignee_id = $data['assignee_id'] ?? null;
        $ticket->save();

        return back()->with('success', 'Ticket assigned.');
    }

    protected function authorizeView(Ticket $ticket): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        if ($ticket->requester_id === $user->id || $ticket->assignee_id === $user->id) {
            return;
        }

        abort(403);
    }

    protected function authorizeManage(Ticket $ticket): void
    {
        $user = auth()->user();

        if ($user->isAdmin() || $ticket->assignee_id === $user->id) {
            return;
        }

        abort(403);
    }
}

