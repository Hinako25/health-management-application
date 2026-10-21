@php
    $guides = config('operationguide.items');
@endphp

<div x-data="{ tab: 'work' }" class="bg-white p-4 rounded-lg shadow-md w-11/12 lg:w-4/5 max-w-5xl mx-auto border-2 border-gray-300">

    {{-- 切り替えボタン --}}
    <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-3">
        @foreach ($guides as $key => $guide)
            <button
                type="button"
                x-on:click="tab = '{{ $key }}'"
                x-bind:class="tab === '{{ $key }}' ? 'bg-green-500 text-white border-green-500' : 'bg-[#eceef1] text-gray-700 border-[#eceef1] hover:bg-[#d5d8dc]'"
                class="px-3 py-1 text-sm font-semibold rounded-md border-2 transition-colors cursor-pointer"
            >
                {{ $guide['label'] }}
            </button>
        @endforeach
    </div>

    @foreach ($guides as $key => $guide)
        <div x-show="tab === '{{ $key }}'" @if ($key !== 'work') x-cloak style="display: none" @endif class="mt-4">
            <h2 class="text-xl font-bold text-[#1a1d23]">{{ $guide['title'] }}</h2>

            {{-- home画面のスクリーンショット --}}
            <div class="flex justify-center mt-4">
                <img src="{{ asset($guide['image']) }}" alt="{{ $guide['title'] }}のスクリーンショット" class="w-full md:w-3/5 h-auto">
            </div>

            {{-- 解説 --}}
            <div class="mt-6">
                <p class="text-lg font-bold">解説</p>
                <dl class="mt-2 space-y-3">
                    @foreach ($guide['items'] as $item)
                        <div class="border-l-4 {{ ($item['warning'] ?? false) ? 'border-red-400 bg-red-50' : 'border-green-500 bg-gray-50' }} rounded-r-md px-3 py-2">
                            <dt class="font-semibold">{{ $item['name'] }}</dt>
                            <dd class="text-sm text-gray-700 mt-1 leading-relaxed">{{ $item['text'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    @endforeach
</div>
