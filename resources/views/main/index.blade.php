@extends('layout.index')

@section('content')
  <div class="wrapper-content mt-3">
    @include('layout.messages')
    <form action="{{ route('post-crawl-data') }}" method="post" class="row">
      @csrf
        <div class="col-lg-6 col-sx-12 col-md-6">
          <div class="form-group">
            <label for="exampleInputEmail1">Lựa chọn nền tảng</label>
            <select name="" id="" class="form-control">
              <option value="1">Nền tảng Shopify</option>
            </select>
          </div>
        </div>
        <div class="col-lg-6 col-sx-12 col-md-6">
          <div class="form-group">
            <label for="exampleInputEmail1">Lựa chọn loại</label>
            <select name="" id="" class="form-control">
              <option value="1">One Product</option>
              <option value="2">Collection</option>
            </select>
          </div>
        </div>
        <div class="col-12">
          <div class="form-group">
            <label for="exampleInputEmail1">Nhập domain</label>
            <input type="url" class="form-control" name="domain" value="{{ old('domain') }}" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="http://google.com...">
          </div>
        </div>
        <div class="col-12">
          <div class="text-center">
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
        </div>
    </form>
    @if (Session::has('data'))
      @if (Session::get('file_name'))
        <a href="{{ Session::get('file_name') }}">Download File</a>
      @endif
      <hr>
      <h1>List Data</h1>
      {{ dump(json_encode(Session::get('data'))) }}
    @endif
  </div>
@endsection