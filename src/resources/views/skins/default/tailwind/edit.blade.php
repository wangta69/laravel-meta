{{-- src/resources/views/skins/tailwind/edit.blade.php --}}
@if (isset($meta) && isset($meta->id))
    <div
        {{ isset($attributes) ? $attributes->merge(['class' => 'mx-auto my-6 max-w-4xl px-4']) : 'class="mx-auto my-6 max-w-4xl px-4"' }}>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-6 py-4">
                <h5 class="text-base font-semibold text-gray-900">SEO 메타 정보 수정</h5>
                <span
                    class="inline-flex items-center rounded-md bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-700">
                    ID: {{ $meta->id }}
                </span>
            </div>

            <form method="POST" action="{{ route('meta.admin.edit', [$meta->id]) }}" class="space-y-4 p-6">
                @method('PUT')
                @csrf
                <input type="hidden" name="back" value="{{ request()->path() }}">
                <input type="hidden" name="path" value="{{ request()->path() }}">

                <div>
                    <label for="pondol-meta-title" class="mb-1 block text-sm font-medium text-gray-700">
                        Title
                    </label>
                    <input type="text" id="pondol-meta-title" name="title" value="{{ old('title', $meta->title) }}"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="pondol-meta-keywords" class="mb-1 block text-sm font-medium text-gray-700">
                        Keywords
                    </label>
                    <input type="text" id="pondol-meta-keywords" name="keywords"
                        value="{{ old('keywords', $meta->keywords) }}"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="pondol-meta-description" class="mb-1 block text-sm font-medium text-gray-700">
                        Description
                    </label>
                    <textarea id="pondol-meta-description" name="description" rows="2"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $meta->description) }}</textarea>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        업데이트
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
