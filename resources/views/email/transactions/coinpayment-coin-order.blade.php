@include('email.header_new')
<h3>{{__('Hello')}}, {{ $data->first_name.' '.$data->last_name  }}</h3>
<p>
{{__('Request submitted successful,please send ')}} {{$data->order->btc.' '.$data->order->coin_type}} {{__(' with this address')}}</p>
<div class="user-profile-img">
    @if(isset($data->order->address))  {!! QrCode::size(300)->generate($data->order->address); !!} @endif
</div>
<p>{{__('Address')}} : {{$data->order->address}}</p>
<p>{{__('Payable Coin')}} : {{$data->order->btc.' '.$data->order->coin_type}}</p>

<p>
    {{__('Thanks a lot for being with us.')}} <br/>
    {{allSetting()['app_title']}} Team
</p>
@include('email.footer_new')