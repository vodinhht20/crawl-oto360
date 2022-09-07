<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Crawl Data</title>
</head>
<body>



</body>
</html>



<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <style>
            body {
                font-family: 'Nunito', sans-serif;
            }
        </style>
    </head>
    <body>
        <div class="container">
          <h1>Nhập trang web muốn crawl</h1>
          <hr>
          <form method="post">
            @csrf
            <div class="form-group">
              <label for="inp-domain">Nhập domain</label>
              {{ csrf_field() }}
              <input type="hidden" class="rule" name="rule" value="000000000">
              <input type="url" class="form-control" name="domain" id="inp-domain" aria-describedby="emailHelp" placeholder="http://google.com...">
              <div id="loading"></div>
            </div>
            <button type="button" class="btn btn-primary" id="btn-submit">Submit</button>
            <div id="data"></div>
          </form>
        </div>
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
        <script>
          $('#btn-submit').on('click', async () => {
            let domain = $('#inp-domain').val();
            $("#loading").html("<p class='text-danger'>Vui lòng đợi, chúng tôi đang xử lý !</p>");
            const respone = await axios.post("{{ route('post-crawl-data') }}", { domain });
            $("#loading").html("<p class='text-success'>Đã xử lý</p>");
            $("#data").html(JSON.stringify(respone?.data));
            console.log("respone.data", respone?.data);
          })
        </script>
    </body>
</html>
