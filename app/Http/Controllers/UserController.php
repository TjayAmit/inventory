<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    private const LOG = 'users';
    private const FIELDS = ['name', 'email', 'branch_id', 'is_active'];

    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request)
    {
        return Inertia::render('users/index', [
            'data'    => $this->userService->list($request)->withQueryString(),
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    public function create()
    {
        return Inertia::render('users/create');
    }

    public function store(UserRequest $request)
    {
        $user = $this->userService->create($request);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['attributes' => $user->only(self::FIELDS)])
            ->event('created')
            ->log('created');

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        return Inertia::render('users/show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user)
    {
        return Inertia::render('users/edit', [
            'user' => $user,
        ]);
    }

    public function update(UserRequest $request, User $user)
    {
        $before = $user->only(self::FIELDS);

        $updated = $this->userService->update($request, $user);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($updated)
            ->withProperties(['old' => $before, 'attributes' => $updated->only(self::FIELDS)])
            ->event('updated')
            ->log('updated');

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $snapshot = $user->only(self::FIELDS + ['email']);

        $this->userService->delete($user);

        activity(self::LOG)
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['attributes' => $snapshot])
            ->event('deleted')
            ->log('deleted');

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
