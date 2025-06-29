@extends('layouts.app')

@section('title', 'INDEX')

@section('content')
    <div class="w-full flex justify-center items-center flex-col min-h-[80vh]">
        <div class="w-full flex justify-center items-center my-5">
            <img src="{{ asset('images/innova_color_icon.png') }}" alt="" class="w-1/4">
        </div>
        <div class="w-full text-center text-2xl mt-5">
           <h1>Bienvenido <strong> {{ Auth::user()->name }} </strong></h1>
           <h2>Sistema de inventario Tapiceria</h2>
        </div>
    </div>
    
@endsection