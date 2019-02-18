@extends('layout.tpl')

@section('main-content')

<?php
    $customer_cell = array_get($session, 'customer_cell');
    $customer_cellnumbers = array_get($session, 'customer_cellnumbers', array());
    $sms_notify = array_get($session, 'sms_notify');
    $cell_valid = cellno_valid($customer_cell);
    $email = array_get($session, 'email');
    $email_notify = array_get($session, 'email_notify');
    $email_valid = ($email != '*no email address*');
?>

<form id="login" title="Login" method="post">
    <input id="account" name="account" type="hidden">
    <div class="form-group">
        <h3 class="customer-info">Meter No: <b>{{ array_get($session, 'meter', 'undefined') }}</b></h3>
        <h4 class="customer-info">Email: <b>{{ $email }}</b></h4>
        <h4 class="customer-info">Cell:
            <select style="font-weight:bold; background-color: white; border: none; cursor: pointer" name="cellno" id="cellno">
              @foreach($customer_cellnumbers as $number)
              <option value="{{ $number }}"{{ $number == $customer_cell  ? ' selected' : ''}}>{{ $number }}</option>
              @endforeach
            </select>
        </h4>
    </div>
    <hr>
    <br>
    <div class="form-group">
        <label class="control-label" for="amount">Amount</label>
        <div id="amount-field">
            <div class="input-group">
                <span class="input-group-addon in">R</span>
                <input type="text" class="form-control" id="amount" name="amount" value="" autocomplete="off" autofocus="true">
            </div>
            <span id="amount-error" class="text-danger"></span>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label" for="amount2">Confirm Amount</label>
        <div id="amount2-field">
            <div class="input-group">
                <span class="input-group-addon in">R</span>
                <input type="text" class="form-control" id="amount2" name="amount2" value="" autocomplete="off">
            </div>
            <span id="amount2-error" class="text-danger"></span>
        </div>
    </div>
    <br>
    <div class="form-group">
        <input type="checkbox" id="sms" name="sms_notify"{{($cell_valid && $sms_notify)?'checked':''}}{{$cell_valid?'':' disabled'}}> <label for="sms"> SMS Notify</label>
        &nbsp;&nbsp;&nbsp;
        <input type="checkbox" id="email" name="email_notify"{{($email_valid && $email)?'checked':''}}{{$email_valid?'':' disabled'}}> <label for="email"> Email Notify</label>
    </div>
    <button class="btn btn-primary" type="submit">Buy</button>
</form>

<br>

{*<a href="?r=test/phpmail">Test PHP Mailer</a><br>

<br>
*}

@stop


@section('scripts')

$('#login').isHappy({
    fields: {
        '#account': {
            required: true,
            message: 'Please select an account to use',
            test: function(val) { return (val > 0); }
        },
        '#amount': {
            required: true,
            clean: function(val) { return val.replace(/[^0-9\.]+/g, ''); },
            test: function(val) { return (val > 0 && val < 10000); }
        },
        '#amount2': {
            required: true,
            clean: function(amount2) { return amount2.replace(/[^0-9\.]+/g, ''); },
            test: function(amount2, amount) { return (amount2 === amount); },
            arg: function () { return $('#amount').val(); }
        },
    },
    unHappy: function() {
        var isUnhappy = $('#account_unhappy');

        if(isUnhappy.length) {
            $('#account').focus();
        }

        var isUnhappy1 = $('#amount_unhappy');

        if(isUnhappy1.length) {
            var val = $('#amount').val();
            if(val >= 10000) {
                $('#amount-error').html('Amount shoud be less than R10 000!');
            }
            else {
                $('#amount-error').html('Amount is Required!');
            }
            if(!isUnhappy.length) {
                $('#amount').focus();
            }
        }
        else {
            $('#amount-error').html('');
        }

        var isUnhappy2 = $('#amount2_unhappy');

        if(isUnhappy2.length) {
            $('#amount2-error').html('Confirmation should match the first amount!');
            if(!isUnhappy1.length) {
                $('#amount2').focus();
            }
        }
        else {
            $('#amount2-error').html('');
        }
    },
    classes: {
        message: 'text-danger'
    }
});

$('#account').val($('#cp-acc').val());

@stop
