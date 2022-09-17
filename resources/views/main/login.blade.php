@extends('layout.index')
@section('content')
  <div class="wrapper-content mt-3 row justify-content-center">
    <div class="col-lg-6 col-md-8 col-sx-10">
      <h4>Đăng nhập</h4>
      @include('layout.messages')
      <form class="mt-4" method="POST" action="{{ route("post-login") }}">
        @csrf
        <div class="form-group">
          <label>Nhập tên tài khoản</label>
          <input type="text" name="account" class="form-control" placeholder="Nhập tên tài khoản ...">
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" class="form-control" placeholder="Password">
        </div>
        <div class="text-center">
          <button type="submit" class="btn btn-primary">Login</button>
        </div>
      </form>
    </div>
    </div>
@endsection