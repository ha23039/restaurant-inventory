<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display a listing of users
     */
    public function index(Request $request): Response
    {
        $users = $this->userService->getPaginated(
            $request->only(['role', 'is_active', 'search']),
            $request->get('per_page', 15)
        );

        return Inertia::render('Users/Index', [
            'users' => UserResource::collection($users),
            'filters' => $request->only(['role', 'is_active', 'search']),
            'statistics' => $this->userService->getStatistics(),
            'roles' => $this->userService->getAvailableRoles(),
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,chef,almacenero,cajero',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        try {
            $this->userService->create($validated);

            return redirect()
                ->route('users.index')
                ->with('success', 'Usuario creado exitosamente');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al crear el usuario: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified user
     */
    public function show(User $user): Response
    {
        return Inertia::render('Users/Show', [
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,chef,almacenero,cajero',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        try {
            $this->userService->update($user, $validated);

            return redirect()
                ->route('users.index')
                ->with('success', 'Usuario actualizado exitosamente');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al actualizar el usuario: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent deleting the currently authenticated user
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'No puedes eliminar tu propio usuario']);
        }

        try {
            $this->userService->delete($user);

            return redirect()
                ->route('users.index')
                ->with('success', 'Usuario eliminado exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar el usuario: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        // Prevent deactivating the currently authenticated user
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'No puedes desactivar tu propio usuario']);
        }

        try {
            $updatedUser = $this->userService->toggleStatus($user);

            $message = $updatedUser->is_active
                ? 'Usuario activado exitosamente'
                : 'Usuario desactivado exitosamente';

            return redirect()
                ->route('users.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cambiar el estado: ' . $e->getMessage()]);
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $this->userService->resetPassword($user, $validated['new_password']);

            return redirect()
                ->route('users.index')
                ->with('success', 'Contraseña reseteada exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al resetear la contraseña: ' . $e->getMessage()]);
        }
    }
}
