@if (Session::has('message.error'))
    <div class="alert alert-danger" role="alert">
        <h5>Đã có lỗi xảy ra !</h5>
        <span>{{ Session::get('message.error') }}</span>
    </div>
@endif
@if (Session::has('message.success'))
    <div class="alert alert-success" role="alert">
        <h5>Thành công !</h5>
        <span>{{ Session::get('message.success') }}</span>
      </div>
@endif