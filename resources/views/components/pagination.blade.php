{{-- ======================================== --}}
{{-- 3. PAGINATION COMPONENT (CUSTOM) --}}
{{-- ======================================== --}}
{{-- File: resources/views/components/pagination.blade.php --}}

@if ($paginator->hasPages())
    <nav class="flex justify-center items-center space-x-2">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-2 bg-gray-700 text-gray-500 rounded cursor-not-allowed">
                ←
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 bg-green-400 hover:bg-green-500 text-black rounded transition">
                ←
            </a>
        @endif

        {{-- Page Number Display --}}
        <div class="px-4 py-2 bg-gray-800 rounded">
            <span class="text-green-400">{{ $paginator->currentPage() }}</span>
            <span class="text-gray-400">/</span>
            <span>{{ $paginator->lastPage() }}</span>
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 bg-green-400 hover:bg-green-500 text-black rounded transition">
                →
            </a>
        @else
            <span class="px-3 py-2 bg-gray-700 text-gray-500 rounded cursor-not-allowed">
                →
            </span>
        @endif
    </nav>
@endif