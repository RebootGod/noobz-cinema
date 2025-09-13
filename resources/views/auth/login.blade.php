{{-- ======================================== --}}
{{-- 2. LOGIN PAGE --}}
{{-- ======================================== --}}
{{-- File: resources/views/auth/login.blade.php --}}

@extends('layouts.app')

@section('title', 'Login - Noobz Cinema')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center">
    {{-- Movie cards background --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="flex justify-around opacity-20">
            @for($i = 0; $i < 5; $i++)
            <div class="w-48 h-72 bg-gray-700 rounded-xl mt-20"></div>
            @endfor
        </div>
    </div>

    {{-- Login Form --}}
    <div class="relative z-10 bg-green-400 p-8 rounded-2xl w-full max-w-md">
        <h2 class="text-2xl font-bold text-black text-center mb-6">LOGIN</h2>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4">
                <input 
                    type="text" 
                    name="username" 
                    placeholder="Username"
                    value="{{ old('username') }}"
                    class="w-full px-4 py-3 rounded-lg bg-white text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-600"
                    required
                    autofocus
                >
                @error('username')
                    <p class="text-red-700 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <input 
                    type="password" 
                    name="password" 
                    placeholder="Password"
                    class="w-full px-4 py-3 rounded-lg bg-white text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-600"
                    required
                >
                @error('password')
                    <p class="text-red-700 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="flex items-center text-black">
                    <input type="checkbox" name="remember" class="mr-2">
                    <span>Remember me</span>
                </label>
            </div>

            <button 
                type="submit"
                class="w-full bg-white hover:bg-gray-100 text-black font-bold py-3 rounded-lg transition duration-200"
            >
                SUBMIT
            </button>
        </form>

        <div class="mt-6 text-center text-black">
            <p>Tidak punya Invite Code? <a href="https://t.me/noobzspace" class="underline hover:no-underline">t.me/noobzspace</a></p>
        </div>
    </div>
</div>
@endsection