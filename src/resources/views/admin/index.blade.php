@section('title', '메타관리')
<x-dynamic-component 
  :component="config('pondol-meta.component.admin.layout')" 
  :path="['메타관리', '리스트']"> 

<div class="p-3 mb-4 bg-light rounded-3">
  <h2 class="fw-bold">메타리스트</h2>

  <div class="card">
    <div class="card-body">
      <div>메타 확인 및 수정이 가능합니다.</div>
    </div><!-- .card-body -->
  </div><!-- .card -->
</div>


<form method="get" action="{{ route('meta.admin.index') }}" id="search-form" >
  <div class="card mt-1 p-2">
    <div class="card-body">
      <div class="row">

        <div class="col-6">
   
            <div class="input-group">
              <select class="form-select" name="sk">
                <option value="metas.name" @if( request()->get('sk') == 'metas.name') selected="selected" @endif >Route Name</option>
                <option value="metas.title" @if( request()->get('sk') == 'metas.title') selected="selected" @endif >Title</option>
                <option value="metas.keywords" @if( request()->get('sk') == 'metas.keywords') selected="selected" @endif >Keyword</option>
              </select>
              <input type="text" name="sv" value="{{ request()->sv}}" placeholder="검색어를 입력해주세요." class="form-control">
              <button class="btn btn-success btn-serch-keyword">검색</button>
            </div>
        </div>

        <div class="ps-5 col-6">
          <div class="input-group mb-1">
            <button type="button" class="btn btn-light act-set-date" user-attr-term="0">오늘</button>
            <button type="button" class="btn btn-light act-set-date" user-attr-term="6">7일</button>
            <button type="button" class="btn btn-light act-set-date" user-attr-term="14">15일</button>
            <button type="button" class="btn btn-light act-set-date" user-attr-term="29">1개월</button>
            <button type="button" class="btn btn-light act-set-date" user-attr-term="179">6개월</button>
          </div>

          <div class="input-group">
            <input type="text" name="from_date" class="form-control" id="from-date" value="{{ request()->from_date}}" readonly>
            <i class="fa fa-calendar from-calendar input-group-text"></i>
            <span class="col-1 text-center">∼</span>
            <input type="text" name="to_date" class="form-control" id="to-date" value="{{ request()->to_date}}" readonly>
            <i class="fa fa-calendar to-calendar input-group-text"></i>
            <button class="btn btn-success btn-serch-date">조회</button>
          </div>
        </div>

      </div> <!-- .row  -->
    </div><!-- .card-body -->
  </div><!-- .card -->
</form>

<div class="card mt-1">
  <div class="card-body">
    <table class="table table-borderless table-striped listTable">
      <col width="*">
      <col width="*">
      <col width="*">
      <col width="*">
      <col width="*">
      <col width="*">
      <col width="120px">
      <thead>
        <tr>
          <th class="text-center">
            Id
          </th>
          <th class="text-center">
            Route Name
          </th>
          <th class="text-center">
            Route Parameter
          </th>
          <th class="text-center">
            Title
          </th>
          <th class="text-center">
            Keyword
          </th>
          <th class="text-center">
            description
          </th>
          <th class="text-center">
            
          </th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $item)
        <tr user-attr-id="{{ $item->id }}">
          <td class="text-center">{{ $item->id }}</td>
          <td class="">{{ $item->name }}</a></td>
          <td class="">{{ $item->params }}</td>
          <td class="">{{ $item->title }}</td>
          <td class="">{{ $item->keywords }}</td>
          <td class="">
            <div style="max-width: 200px; max-height: 50px; overflow: hidden;">
              {{ $item->description }}
            </div>
          </td>
          <td class="text-center">
            <a href="{{route('meta.admin.edit', [$item->id])}}" class="btn btn-primary btn-sm">수정</a>
            <button type="button" class="btn btn-danger btn-sm act-delete">삭제</button>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="text-center">
            디스플레이할 데이타가 없습니다.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div><!-- .card-body -->
  <div  class="card-footer">
    {{ $items->links("pagination::bootstrap-4") }}
  </div><!-- .card-footer -->
</div><!-- .card -->

@section('scripts')
@parent
<script>
$(function(){
  $('.act-delete').on('click', function(){
    var id = $(this).parents('tr').attr('user-attr-id');

    ROUTE.ajaxroute('delete', 
    {route: 'meta.admin.delete', segments: [id]}, 
    function(resp) {
      if(resp.error) {
        showToaster({title: '알림', message: resp.error});
      } else {
        window.location.reload();
      }
    })
  })
})
</script>
@endsection
</x-dynamic-component>
