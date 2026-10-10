@props([
    'icon' => 'solar:box-bold',
    'title' => 'Data kosong',
    'description' => 'Belum ada data yang tersedia.',
    'colspan' => null,
])

@if ($colspan)
    <tr>
        <td colspan="{{ $colspan }}" class="px-6 py-14 text-center">
            <div class="mx-auto flex w-fit max-w-full flex-col items-center justify-center text-center">
                <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-3xl bg-gray-100 text-gray-400">
                    <iconify-icon icon="{{ $icon }}" width="38" height="38"></iconify-icon>
                </div>

                <h2 class="text-lg font-bold text-gray-700">
                    {{ $title }}
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $description }}
                </p>

                @if (trim($slot) !== '')
                    <div class="mt-5 flex justify-center">
                        {{ $slot }}
                    </div>
                @endif
            </div>
        </td>
    </tr>
@else
    <div class="px-6 py-14 text-center">
        <div class="mx-auto flex w-fit max-w-full flex-col items-center justify-center">
            <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-3xl bg-gray-100 text-gray-400">
                <iconify-icon icon="{{ $icon }}" width="38" height="38"></iconify-icon>
            </div>

            <h2 class="text-lg font-bold text-gray-700">
                {{ $title }}
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                {{ $description }}
            </p>

            @if (trim($slot) !== '')
                <div class="mt-5 flex justify-center">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>
@endif
