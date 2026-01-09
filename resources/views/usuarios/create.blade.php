@extends('layouts.admin')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <h1><i class="fas fa-user-plus"></i> Nuevo Usuario</h1>
    </div>
</section>

<section class="content">
<div class="container-fluid">

<div class="card shadow-sm">
    <div class="card-header bg-primary">
        <h3 class="card-title">Datos del usuario</h3>
    </div>

    <form method="POST" action="{{ route('usuarios.store') }}">
    @csrf

    <div class="card-body">

        <div class="form-group">
            <label>Nombre</label>
            <input class="form-control" name="name" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input class="form-control" type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Contraseña</label>
            <input class="form-control" type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Rol</label>
            <select class="form-control" name="role" required>
                @foreach($roles as $role)
                    <option value="{{ $role }}">{{ $role }}</option>
                @endforeach
            </select>
        </div>

    </div>

    <div class="card-footer text-right">
        <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
            Cancelar
        </a>
        <button class="btn btn-success">
            <i class="fas fa-save"></i> Guardar
        </button>
    </div>

    </form>
</div>

</div>
</section>

@endsection
