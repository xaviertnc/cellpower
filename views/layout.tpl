<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mr Prepaid - Cell Power Interface</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    @yield('styles')
</head>
<body>
<div id="page" class="container">
    <?php
        $account = array_get($session, 'account');
        $accounts = array_get($session, 'accounts');
    ?>
    <header>
        <div class="row">
            <div class="col-sm-3"></div>
            <div class="col-sm-6">
                <img src="img/tshwane_logo.gif" id="logo">
            </div>
            <div class="col-sm-3">
                <div class="user-info">User: {{ array_get($session,'user_name','guest') }}</div>
                <div class="user-info">
                    Cellpower Acc:
                    <select id="cp-acc" name="cp-acc" style="background-color:#444; cursor:pointer; color:white; border:none" onchange="$('#account').val(this.value);">
                      <option value="">- Select -</option>
                      @foreach($accounts as $number)
                      <option value="{{ $number }}"{{ $number == $account  ? ' selected' : ''}}>{{ $number }}</option>
                      @endforeach
                    </select>
                </div>
                @if(isset($session['cellpower_session']))
                <div class="user-info">Session: {{ $session['cellpower_session'] }}</div>
                @endif
            </div>
        </div>
    </header>

    <hr>

    @if(isset($flash))
    @if($flash->has('success'))
    <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {{ $flash->get('success') }}
    </div>
    @endif

    @if($flash->has('danger'))
    <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {{ $flash->get('danger') }}
    </div>
    @endif
    @endif

    <section>
        @yield('main-content')
    </section>

    <hr>

    <footer>
        &copy; Mr Prepaid - {{ date('Y') }}
    </footer>

</div>

<script src="js/jquery.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/happy.min.js"></script>

<script>
$(document).ready(function(){
    @yield('scripts')
});
</script>

</body>
</html>
