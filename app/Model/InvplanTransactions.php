<?php

namespace App\Model;
use App\User;
use Illuminate\Database\Eloquent\Model;

class InvplanTransactions extends Model
{

    protected $fillable=['user','status','plan','amount','type'];

    public function duser()
    {
        return $this->hasOne(User::class,'id','user');
    }


}
