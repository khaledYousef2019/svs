@include('email.header_new')
<h3>{{__('Hello')}}, {{ $data->first_name.' '.$data->last_name  }}</h3>
<p>
    {{__('We need to verify your email address. In order to verify your account please click on the following link or paste the link on address bar of your browser and hit -')}}
</p>

<p>
    {{__('Thanks a lot for being with us.')}} <br/>
    {{allSetting()['app_title']}}
</p>
@include('email.footer_new')