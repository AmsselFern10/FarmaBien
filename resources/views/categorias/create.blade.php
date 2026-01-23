{{-- resources/views/categorias/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nueva Categoría')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Nueva Categoría
    </h2>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <form action="{{ route('categorias.store') }}" method="POST">
                @csrf
                @include('categorias.form')
                
                <div class="flex items-center justify-end mt-6 space-x-2">
                    <a href="{{ route('categorias.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Cancelar
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Crear Categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

