<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPath extends Model
{
    use HasFactory;

    protected $table = 'payment_paths';

    protected $fillable = [
        'name'
    ];

    //perform selection
    public static function selectPaymentPaths($id, $name){

        $payment_path_query = PaymentPath::whereNotNull('id');

        if($id != null){
            $payment_path_query->where('payment_paths.id', $id);
        }
        elseif($name != null){
            $payment_path_query->where('payment_paths.name', $name);
        }



        return $payment_path_query->get()->map(function ($payment_path) {
            $payment_path_details = [
                'id' => $payment_path->id,
                'name' => $payment_path->name,
            ];

            return $payment_path_details;
        });

    }
}
