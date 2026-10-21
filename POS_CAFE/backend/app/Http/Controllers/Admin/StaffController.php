<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaffRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index()
    {
        $staff = User::with('role')->latest()->paginate(15);
        $roles = Role::all();

        return view('staff.index', compact('staff', 'roles'));
    }

    public function create()
    {
        $roles = auth()->user()->isAdmin() ? Role::all() : Role::where('slug', '!=', 'admin')->get();
        return view('staff.create', compact('roles'));
    }

    public function store(StaffRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active', true);

        $user = User::create($data);
        AuditService::log('staff_created', $user, null, ['email' => $user->email, 'role_id' => $user->role_id]);

        return redirect()->route('staff.index')->with('success', "Staff account '{$user->name}' created.");
    }

    public function edit(User $staff)
    {
        $roles = Role::all();
        return view('staff.edit', compact('staff', 'roles'));
    }

    public function update(StaffRequest $request, User $staff)
    {
        $data = $request->validated();

        // Prevent users from changing their own role
        if ($request->user()->id === $staff->id && (int) $data['role_id'] !== $staff->role_id) {
            return back()->withErrors(['role_id' => 'You cannot change your own role.']);
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active', false);

        // Prevent self-deactivation
        if ($request->user()->id === $staff->id && !$data['is_active']) {
            return back()->withErrors(['is_active' => 'You cannot deactivate your own account.']);
        }

        $old = $staff->toArray();
        $staff->update($data);

        AuditService::log('staff_updated', $staff, $old, $staff->toArray());

        return redirect()->route('staff.index')->with('success', "Staff account '{$staff->name}' updated.");
    }

    public function destroy(User $staff)
    {
        if (!Gate::allows('delete', $staff)) {
            return back()->withErrors(['staff_error' => 'Cannot delete this account (self-deletion or last active administrator).']);
        }

        $name = $staff->name;
        $staff->is_active = false;
        $staff->save();

        AuditService::log('staff_deactivated', $staff, null, ['name' => $name]);

        return redirect()->route('staff.index')->with('success', "Staff member '{$name}' deactivated.");
    }
}
