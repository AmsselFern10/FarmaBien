@extends('layouts.admin')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <h1><i class="fas fa-user-edit"></i> Editar Usuario</h1>
    </div>
</section>

<section class="content">
<div class="container-fluid">

<div class="card shadow-sm">
    <div class="card-header bg-warning">
        <h3 class="card-title">Actualizar información</h3>
    </div>

    <form method="POST" action="{{ route('usuarios.update', $usuario) }}">
    @csrf
    @method('PUT')

    <div class="card-body">

        <div class="form-group">
            <label>Nombre</label>
            <input class="form-control" name="name"
                   value="{{ $usuario->name }}" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input class="form-control" type="email" name="email"
                   value="{{ $usuario->email }}" required>
        </div>

        <div class="form-group">
            <label>Nueva contraseña</label>
            <input class="form-control" type="password" name="password"
                   placeholder="Dejar vacío para no cambiar">
        </div>

        <div class="form-group">
            <label>Rol</label>
            <select class="form-control" name="role" required>
                @foreach($roles as $role)
                    <option value="{{ $role }}"
                        {{ $usuario->hasRole($role) ? 'selected' : '' }}>
                        {{ $role }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>

    <div class="card-footer text-right">
        <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
            Cancelar
        </a>
        <button class="btn btn-primary">
            <i class="fas fa-sync"></i> Actualizar
        </button>
    </div>

    </form>
</div>

</div>
</section>

@endsection
