{{-- src/resources/views/skins/default/edit.blade.php --}}
@if (isset($meta) && isset($meta->id))
    <div {{ isset($attributes) ? $attributes->merge(['class' => 'container my-4']) : 'class="container my-4"' }}>
        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fs-6 fw-semibold">SEO 메타 정보 수정</h5>
                <span class="badge text-bg-secondary">ID: {{ $meta->id }}</span>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('meta.admin.edit', [$meta->id]) }}">
                    @method('PUT')
                    @csrf
                    <input type="hidden" name="back" value="{{ request()->path() }}">
                    <input type="hidden" name="path" value="{{ request()->path() }}">

                    <div class="mb-3">
                        <label for="pondol-meta-title" class="form-label fw-medium">Title</label>
                        <input type="text" id="pondol-meta-title" name="title"
                            value="{{ old('title', $meta->title) }}" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="pondol-meta-keywords" class="form-label fw-medium">Keywords</label>
                        <input type="text" id="pondol-meta-keywords" name="keywords"
                            value="{{ old('keywords', $meta->keywords) }}" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="pondol-meta-description" class="form-label fw-medium">Description</label>
                        <textarea id="pondol-meta-description" name="description" rows="2" class="form-control">{{ old('description', $meta->description) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">업데이트</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
