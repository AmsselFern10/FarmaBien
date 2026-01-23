<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver usuarios')->only(['index', 'show']);
        $this->middleware('permission:crear usuarios')->only(['create', 'store']);
        $this->middleware('permission:editar usuarios')->only(['edit', 'update']);
        $this->middleware('permission:desactivar usuarios')->only(['destroy']);
        $this->middleware('permission:asignar roles')->only(['roles', 'updateRoles']);
    }

    public function index(Request $request)
    {
        $query = User::with('roles')->orderBy('name');

        if ($request->filled('buscar')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->buscar}%")
                  ->orWhere('email', 'like', "%{$request->buscar}%");
            });
        }

        if ($request->filled('rol')) {
            $query->role($request->rol);
        }

        $usuarios = $query->paginate(20);
        $roles = Role::all();

        return view('usuarios.index', compact('usuarios', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('usuarios.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'rol' => ['required', 'exists:roles,name'],
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($request->rol);

            return redirect()
                ->route('usuarios.index')
                ->with('success', "Usuario '{$user->name}' creado correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al crear el usuario: ' . $e->getMessage());
        }
    }

    public function show(User $usuario)
    {
        $usuario->load('roles');
        return view('usuarios.show', compact('usuario'));
    }

    public function edit(User $usuario)
    {
        $roles = Role::all();
        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$usuario->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'rol' => ['required', 'exists:roles,name'],
        ]);

        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $usuario->update($data);
            $usuario->syncRoles([$request->rol]);

            return redirect()
                ->route('usuarios.show', $usuario)
                ->with('success', "Usuario '{$usuario->name}' actualizado correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el usuario: ' . $e->getMessage());
        }
    }

    public function destroy(User $usuario)
    {
        // No permitir eliminar el propio usuario
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propio usuario.');
        }

        try {
            $usuario->delete();
            
            return redirect()
                ->route('usuarios.index')
                ->with('success', "Usuario '{$usuario->name}' desactivado correctamente.");
                
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al desactivar el usuario: ' . $e->getMessage());
        }
    }
}