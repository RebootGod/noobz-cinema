{{-- ======================================== --}}
{{-- 3. REGISTER PAGE --}}
{{-- ======================================== --}}
{{-- File: resources/views/auth/register.blade.php --}}

@extends('layouts.app')

@section('title', 'Register - Noobz Cinema')

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

    {{-- Register Form --}}
    <div class="relative z-10 bg-green-400 p-8 rounded-2xl w-full max-w-md">
        <h2 class="text-2xl font-bold text-black text-center mb-6">REGISTER</h2>

        <form method="POST" action="{{ route('register') }}" id="registerForm">
            @csrf

            <div class="mb-4">
                <input 
                    type="text" 
                    name="username" 
                    placeholder="Username"
                    value="{{ old('username') }}"
                    class="w-full px-4 py-3 rounded-lg bg-white text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-600"
                    required
                    pattern="[a-zA-Z0-9_]{3,20}"
                    title="Username hanya boleh huruf, angka, underscore (3-20 karakter)"
                >
                @error('username')
                    <p class="text-red-700 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <input 
                    type="email" 
                    name="email" 
                    placeholder="Email"
                    value="{{ old('email') }}"
                    class="w-full px-4 py-3 rounded-lg bg-white text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-600"
                    required
                >
                @error('email')
                    <p class="text-red-700 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <input 
                    type="password" 
                    name="password" 
                    placeholder="Password"
                    class="w-full px-4 py-3 rounded-lg bg-white text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-600"
                    required
                    minlength="6"
                >
                @error('password')
                    <p class="text-red-700 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <input 
                    type="password" 
                    name="password_confirmation" 
                    placeholder="Konfirmasi Password"
                    class="w-full px-4 py-3 rounded-lg bg-white text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-600"
                    required
                    minlength="6"
                >
            </div>

            <div class="mb-6">
                <input 
                    type="text" 
                    name="invite_code" 
                    placeholder="Invite Code"
                    value="{{ old('invite_code') }}"
                    class="w-full px-4 py-3 rounded-lg bg-white text-black placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-600"
                    required
                >
                @error('invite_code')
                    <p class="text-red-700 text-sm mt-1">{{ $message }}</p>
                @enderror
                <div id="inviteCodeFeedback" class="text-sm mt-1"></div>
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

@push('scripts')
<script>
// Live invite code validation
document.querySelector('input[name="invite_code"]').addEventListener('blur', function() {
    const code = this.value;
    const feedback = document.getElementById('inviteCodeFeedback');
    
    if (code.length > 0) {
        fetch(`/check-invite-code?code=${code}`)
            .then(response => response.json())
            .then(data => {
                if (data.valid) {
                    feedback.innerHTML = '<span class="text-green-700">✓ ' + data.message + '</span>';
                } else {
                    feedback.innerHTML = '<span class="text-red-700">✗ ' + data.message + '</span>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    } else {
        feedback.innerHTML = '';
    }
});
</script>
@endpush