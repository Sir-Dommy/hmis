<?php

namespace App\Models\Bill;

use App\Exceptions\TransactionException;
use App\Models\Admin\Scheme;
use App\Models\User;
use App\Utils\CustomUserRelations;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'transactions';

    protected $fillable = [
        'bill_id',
        'transaction_reference',
        'third_party_reference',
        'patient_account_no',
        'hospital_account_no',
        'scheme_name',
        'scheme_id',
        'initiation_time',
        'amount',
        'fee',
        'receipt_date',
        'status',
        'is_reversed',
        'reverse_date',
        'reversed_by',
        'reason',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
        'disabled_by',
        'disabled_at',
        'deleted_by',
        'deleted_at'
    ];

    public function bill()
    {
        return $this->belongsTo(BillItem::class, 'bill_id');
    }

    public function reversedBy()
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }

    //perform selection
    public static function selectTransactions($id, $transaction_reference){
        $transactions_query = Bill::with([
            'bill:id,bill_reference',
            'reversedBy:id,email'
        ])->whereNull('bills.deleted_by');

        if($id != null){
            $transactions_query->where('transactions.id', $id);
        }
        elseif($transaction_reference != null){
            $transactions_query->where('transactions.transaction_reference', $transaction_reference);
        }


        else{
            $paginated_transactions = $transactions_query->paginate(10);

            
            $paginated_transactions->getCollection()->transform(function ($transaction) {
                return Transaction::mapResponse($transaction);
            });
    
            return $paginated_transactions;
        }


        return $transactions_query->get()->map(function ($transaction) {
            $transaction_details = Transaction::mapResponse($transaction);

            return $transaction_details;
        });


    }

    public static function createTransaction($bill_id, $third_party_reference, $patient_account_no, $hospital_account_no, $scheme_name, $initiation_time, $amount, $fee, $receipt_date, $status, $reason){
        
        $scheme_name == null ? $scheme_id = Scheme::selectSchemes(null, $scheme_name)[0]['id'] : $scheme_id = null;
        //$existing_scheme = Scheme::selectSchemes(null, $scheme_name);

        $initiation_time == null ?? $initiation_time = Carbon::now();

        $fee == null ?? $fee = 0.0;

        try{
            DB::beginTransaction();

            $created = Transaction::create([
                'bill_id' => $bill_id,
                'transaction_reference' => Transaction::generateUniqueTransactionReferenceNumber(),
                'third_party_reference' => $third_party_reference,
                'patient_account_no' => $patient_account_no,
                'hospital_account_no' => $hospital_account_no,
                'scheme_name' => $scheme_name,
                'scheme_id' => $scheme_id,
                'initiation_time' => $initiation_time,
                'amount' => $amount,
                'fee' => $fee,
                'receipt_date' => $receipt_date,
                'status' => $status,
                'reason' => $reason
            ]);

            DB::commit();
        }

        catch(Exception $e){
            DB::rollBack();

            throw new TransactionException("COULD NOT SAVE TRANSACTION!!!!!!!11");
        }
        

        return Transaction::selectTransactions($created->id, null);
    }

    //function to generate unique transaction reference number
    private static function generateUniqueTransactionReferenceNumber(){

        // Generate a random six-digit number
        $randomNumber = str_pad(mt_rand(1, 999999999999), 12, '0', STR_PAD_LEFT);

        // Add the B prefix
        $transaction_reference = 'T' . $randomNumber;

        // Check if the code already exists in the database
        while (Transaction::where('transaction_reference', $transaction_reference)->exists()) {
            $transaction_reference = str_pad(mt_rand(1, 999999999999), 12, '0', STR_PAD_LEFT);
            $transaction_reference = 'T' . $randomNumber;
        }

        return $transaction_reference;
    }


    private static function mapResponse($transaction){
        return [
            'id' => $transaction->id,
            'transaction_reference'=>$transaction->transaction_reference,
            'third_party_reference'=>$transaction->third_party_reference,
            'bill_id'=>$transaction->bill->id,
            'bill_reference_number'=>$transaction->bill->bill_reference_number,
            'patient_account_no' => $transaction->patient_account_no,
            'hospital_account_no' => $transaction->hospital_account_no,
            'scheme_name' => $transaction->scheme_name,
            'initiation_time' => $transaction->initiation_time,
            'amount' => $transaction->amount,
            'fee' => $transaction->fee,
            'receipt_date' => $transaction->receipt_date,
            'status' => $transaction->status,
            'is_reversed' => $transaction->is_reversed,
            'reverse_date' => $transaction->reverse_date,
            'reason' => $transaction->reason,
            'reversed_by' => $transaction->reversedBy ? $transaction->reversedBy->email : null,
            'created_by' => $transaction->createdBy ? $transaction->createdBy->email : null,
            'created_at' => $transaction->created_at,
            'updated_by' => $transaction->updatedBy ? $transaction->updatedBy->email : null,
            'updated_at' => $transaction->updated_at,
            'approved_by' => $transaction->approvedBy ? $transaction->approvedBy->email : null,
            'approved_at' => $transaction->approved_at,    

        ];
    }
}
