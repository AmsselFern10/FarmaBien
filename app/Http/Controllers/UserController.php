<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginLog;
use App\Models\AuditLog;
use Spatie\Permission\Models\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver usuarios')->only(['index', 'show']);
        $this->middleware('permission:crear usuarios')->only(['create', 'store']);
        $this->middleware('permission:editar usuarios')->only(['edit', 'update']);
        $this->middleware('permission:desactivar usuarios')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = User::with(['roles', 'loginLogs' => function($q){ $q->where('tipo','login')->orderByDesc('created_at')->limit(1); }]);

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('name', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        $usuarios    = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();
        $totalActivos = User::where('active', true)->count();
        $totalRoles   = Role::count();

        return view('usuarios.index', compact('usuarios', 'totalActivos', 'totalRoles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('usuarios.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'active' => $request->boolean('active', true),
            ]);

            $user->syncRoles([$request->role]);

            AuditLog::log('usuarios', 'crear', "Usuario '{$user->name}' ({$user->email}) creado", [
                'user_id' => $user->id,
                'role' => $request->role,
            ]);

            return $user;
        });

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario '{$user->name}' creado exitosamente.");
    }

    public function show(Request $request, User $usuario)
    {
        $usuario->load('roles');

        $logsQuery = LoginLog::where('user_id', $usuario->id)->orderByDesc('created_at');

        if ($request->filled('desde')) {
            $logsQuery->whereDate('created_at', '>=', $request->input('desde'));
        }
        if ($request->filled('hasta')) {
            $logsQuery->whereDate('created_at', '<=', $request->input('hasta'));
        }

        $loginLogs = $logsQuery->paginate(25)->withQueryString();

        return view('usuarios.show', compact('usuario', 'loginLogs'));
    }

    public function edit(User $usuario)
    {
        $roles = Role::all();
        $usuario->load('roles');
        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $usuario)
    {
        DB::transaction(function () use ($request, $usuario) {
            $locked = User::where('id', $usuario->id)->lockForUpdate()->firstOrFail();

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'active' => $request->boolean('active', true),
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $locked->update($data);
            $locked->syncRoles([$request->role]);

            AuditLog::log('usuarios', 'actualizar', "Usuario '{$locked->name}' actualizado", [
                'user_id' => $locked->id,
                'role' => $request->role,
                'active' => $locked->active,
            ]);
        });

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario '{$usuario->name}' actualizado exitosamente.");
    }

    public function destroy(User $usuario)
    {
        if ($usuario->id === Auth::id()) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta de usuario.');
        }

        $nuevoEstado = DB::transaction(function () use ($usuario) {
            $locked = User::where('id', $usuario->id)->lockForUpdate()->firstOrFail();
            $estadoBool = !$locked->active;
            $locked->update(['active' => $estadoBool]);

            AuditLog::log(
                'usuarios',
                $estadoBool ? 'activar' : 'desactivar',
                "Usuario '{$locked->name}' " . ($estadoBool ? 'activado' : 'desactivado'),
                ['user_id' => $locked->id]
            );

            return $estadoBool ? 'activado' : 'desactivado';
        });

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario '{$usuario->name}' {$nuevoEstado} correctamente.");
    }
}
