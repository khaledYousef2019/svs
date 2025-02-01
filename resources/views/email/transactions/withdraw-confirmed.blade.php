@include('email.header_new')
<h3>{{__('Dear')}}, {{ $data->first_name.' '.$data->last_name  }}</h3>
<p>
{{__('You\'ve successfully withdrawn ')}}{{$data->trx->amount.' '.$data->trx->wallet->name}}{{__(' from your account')}}
</p>
<div  style="text-align: left; margin:1rem">
<p>
<b>Wtihdraw Address:</b><br>
{{$data->trx->address}}<br>
<b>Transaction ID:</b><br>
{{$data->trx->transaction_hash}}
</p>
</div>
<br>
<p>Don't recognize this activity? Please <a href="https://w-wallet.org/forgot-password">reset you password</a> and contact customer support immediately.</p>
<p>
    {{__('Thanks a lot for being with us.')}} <br/>
    {{allSetting()['app_title']}} Team
</p>
@include('email.footer_new')