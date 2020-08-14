<?php


namespace App\Model;


use App\Traits\ConvertDateTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use ConvertDateTrait;

    public $timestamps = false;

    protected $table = 'transactions';

    protected $fillable = ['user_id', 'amount', 'transfer_code', 'reference', 'trans_ref_id', 'status'];

    protected $dates = ['created_at'];

    protected $casts = [
        'user_id' => 'int',
        'amount' => 'float'
    ];

    /** ---- ELOQUENT RELATIONSHIPS ----  */

    public function users()
    {
        return $this->belongsTo('App\Model\User');
    }

    /** ---- SCOPES ---- */

    public function scopeUserId($query, $id)
    {
        if (empty($id)) {
            return $query;
        }

        return $query->where('transactions.user_id', '=', $id);
    }

    public function scopeTransferCode($query, $transfer_code)
    {
        if (empty($transfer_code)) {
            return $query;
        }

        return $query->where('transactions.transfer_code', '=', $transfer_code);
    }

    public function scopeReference($query, $reference)
    {
        if (empty($reference)) {
            return $query;
        }

        return $query->where('transactions.reference', '=', $reference);
    }

    public function scopeStatus($query, $status)
    {
        if (empty($status)) {
            return $query;
        }

        return $query->where('transactions.status', '=', $status);
    }

    public function scopeFromDate($query, $dateFrom)
    {
        if (empty($dateFrom)) {
            return $query;
        }

        $dateFrom = $this->convertToServerDate($dateFrom);

        return $query->where('transactions.created_at', '>=', DB::raw("TIMESTAMP('$dateFrom')"));
    }

    public function scopeToDate($query, $dateTo)
    {
        if (empty($dateTo)) {
            return $query;
        }

        $dateTo = Carbon::parse($dateTo)->addDay(1);

        $dateTo = $this->convertToServerDate($dateTo);

        return $query->where('transactions.created_at', '<', DB::raw("TIMESTAMP('$dateTo')"));
    }
}
