<?php


namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class RecipientAccount extends Model
{
    public $timestamps = false;

    protected $table = 'recipients_account';

    protected $fillable = ['recipient_id', 'authorization_code', 'account_number', 'bank_code', 'bank_name'];

    protected $dates = ['created_at'];

    /** ---- ELOQUENT RELATIONSHIPS ----  */

    public function recipients()
    {
        return $this->belongsTo('App\Model\Recipient');
    }
}
