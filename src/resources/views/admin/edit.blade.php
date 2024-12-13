@section('title', '메타수정')
<x-dynamic-component 
  :component="config('pondol-meta.component.admin.layout')" 
  :path="['메타관리', '수정']"> 

<div class="p-3 mb-4 bg-light rounded-3">
  <h2 class="fw-bold">메타수정</h2>

  <div class="card">
    <div class="card-body">
      <div>메타 확인 및 수정이 가능합니다.</div>
    </div><!-- .card-body -->
  </div><!-- .card -->
</div>



<div class="card">
  <form method="POST" action="{{ route('meta.admin.edit', [$item->id]) }}">
    @method('PUT')
    @csrf          
    <div class="card-header">
      메타수정
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-2">
          <label class="form-label">Route Name</label>
        </div>
        <div class="col-4">
          <span>{{$item->name}}</span>
        </div>
        <div class="col-2">
            <label class="form-label">Route Parameter</label>
        </div>
        <div class="col-4">
            <p class="form-control-static">{{ $item->params }}</p>
        </div>
      </div>


      <div class="row mt-1">
        <div class="col-2">
          <label class="form-label">Title</label>
        </div>
        <div class="col-4">
          <input type="text" name="title" value="{{$item->title}}" class="form-control">
        </div>
        <div class="col-2">
          <label class="form-label">Keyword<label>
        </div>
        <div class="col-4">
          <input type="text" name="keywords" value="{{$item->keywords}}" class="form-control">
        </div>
      </div>
      <div class="row mt-1">
        <div class="col-2">
          <label for="password" class="form-label">image</label>
        </div>
        <div class="col-10">
          <input type="text" name="image"  value="{{$item->image}}" class="form-control">
        </div>
      </div>

      <div class="row mt-1">
        <div class="col-2">
          <label for="password" class="form-label">description</label>
        </div>
        <div class="col-10">
          <textarea name="description" class="form-control">{{$item->description}}</textarea>
        </div>
      </div>
    </div><!-- card-body -->
    <x-pondol::validation-fail.first />
    <div class="card-footer text-end">
      <button type="submit" class="btn btn-primary btn-sm">
        <i class="fa-regular fa-circle-check"></i> 변경
      </button>
      <a href="{{ URL::previous() }}" type="reset" class="btn btn-danger btn-sm">
        <i class="fa fa-ban"></i> 취소
      </a>
    </div>
  </form>
</div>

@section('scripts')
@parent
@endsection
</x-dynamic-component>
