wfd@include('email.header_new')
<h3>{{__('Deposit Successful')}}</h3>
<p>
{{__('Your deposit of ')}}<b>{{$data->trx->amount.' '.$data->trx->type}}</b>{{__(' is now available in your wfdpwallet Account, log in to check your balance. Read our FAQs if you are running into problems')}}
</p>
<p>Don't recognize this activity? Please <a href="https://w-wallet.org/forgot-password">reset you password</a> and contact customer support immediately.</p>
<p style="font-size: 0.8rem;">This is an automated message, please do not reply</p>
<p>
    {{__('Thanks a lot for being with us.')}} <br/>
    {{allSetting()['app_title']}} Team
</p>
@include('email.footer_new')