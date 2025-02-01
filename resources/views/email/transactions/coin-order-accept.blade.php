@include('email.header_new')
<h3>{{__('Hello')}}, {{ $data->first_name.' '.$data->last_name  }}</h3>
<p>
{{__('Your ')}}{{$data->order->coin}}{{__(' WFDP Coin purchase request has been approved and value added to your wallet, you may login to w-wallet.org App and check the details.')}}
</p>

<p>
    {{__('Thanks a lot for being with us.')}} <br/>
    {{allSetting()['app_title']}} Team
</p>
@include('email.footer_new')