<?php


namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    public $timestamps = false;

    protected $table = 'banks';

    protected $fillable = ['name', 'bank_code', 'slug'];

}
