
@if (count($errors) > 0)
   <div class="alert alert-danger text-left">
        <i class="fa fa-close"></i>
        @foreach ($errors->all() as $error)
            {{ $error }} <br>
        @endforeach
    </div>
@endif
