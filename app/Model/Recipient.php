<?php


namespace App\Model;


use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
    public $timestamps = false;

    protected $table = 'recipients';

    protected $fillable = ['user_id', 'name', 'recipient_code'];

    protected $dates = ['created_at'];

    /** ---- ELOQUENT RELATIONSHIPS ----  */

    public function recipient_account()
    {
        return $this->hasOne('App\Model\RecipientAccount');
    }

    /** ---- SCOPES ----- */

    /**
     * @param $query
     * @param $user_id
     * @return mixed
     */
    public function scopeUserId($query, $user_id)
    {
        if (empty($user_id)) {
            return $query;
        }

        return $query->where('recipients.user_id', '=', $user_id);
    }

    /**
     * @param $query
     * @param $recipient
     * @return mixed
     */
    public function scopeRecipientCode($query, $recipient)
    {
        if (empty($recipient)) {
            return $query;
        }

        return $query->where('recipients.recipient_code', '=', $recipient);
    }

}
