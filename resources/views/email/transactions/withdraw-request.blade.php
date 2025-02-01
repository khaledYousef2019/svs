@include('email.header_new')
<h3>{{__('Dear')}}, {{ $data->first_name.' '.$data->last_name  }}</h3>
<p>
    {{__('You\'ve initiated a request to withdraw ')}}{{$data->trx->amount.' '.$data->trx->coin_type}}{{__(' to the following address:')}}
</p>a
<p>
    Address: <b>{{$data->trx->address}}</b>
</p>
<p>
    Memo:
</p>
<p style="color:red">
    please carefully review the withdraw address and operation information before you proceed. WFDPWALLET will not be responsible for any funds sent to the wrong address.
</p>

<p>Don't recognize this activity? Please <a href="https://w-wallet.org/forgot-password">reset you password</a> and contact customer support immediately.</p>
<p>
    {{__('Thanks a lot for being with us.')}} <br />
    {{allSetting()['app_title']}} Team
</p>
<p style="color:#ccc;font-size:0.8rem"> Automated message. Please do not relpy</p>
@include('email.footer_new')