@include('email.header_new')
<h3>{{__('Hello')}}, {{ $data->first_name.' '.$data->last_name  }}</h3>
<p>
{{__('You Purchased ')}}{{$data->order->coin}}{{__(' WFDP Coin and your request is under review, Please wait for admin approval, we will keep you informed with any updates.')}}
</p>

<p>
    {{__('Thanks a lot for being with us.')}} <br/>
    {{allSetting()['app_title']}} Team
</p>
@include('email.footer_new')