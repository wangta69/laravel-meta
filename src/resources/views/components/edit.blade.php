<div class="container mt-5">
    <form method="POST" action="{{ route('meta.admin.edit', [$meta->id]) }}">
    @method('PUT')
    @csrf
    <input type="hidden" name="back" value="{{request()->path()}}"> 
    <input type="hidden" name="path" value="{{request()->path()}}"> 
        
    <ul>
        <li>title: <input type="text" name="title" value="{{$meta->title}}" class="form-control"></li>
        <li>keywords: <input type="text" name="keywords" value="{{$meta->keywords}}" class="form-control"></li>
        <li>description: <input type="text" name="description" value="{{$meta->description}}" class="form-control"></li>
    </ul>
    <button type="submit">업데이트</button>
    </form>
</div>