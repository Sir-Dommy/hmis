<?php

namespace App\Models\Bill;

use App\Exceptions\InputsValidationException;
use App\Exceptions\NotFoundException;
use App\Models\Admin\ServiceRelated\Service;
use App\Models\Admin\ServiceRelated\ServicePrice;
use App\Models\Patient\Visit;
use App\Models\User;
use App\Utils\APIConstants;
use App\Utils\CustomUserRelations;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class Bill extends Model
{
    use HasFactory; 

    use CustomUserRelations;

    protected $table = 'bills';

    protected $fillable = [
        'bill_reference_number',
        'visit_id',
        'initiated_at',
        'bill_amount',
        'discount',
        'status',
        'reason',
        'is_reversed',
        'reversed_at',
        'reversed_by',
        'expiry_time',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
        'disabled_by',
        'disabled_at',
        'deleted_by',
        'deleted_at'
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class, 'visit_id');
    }

    public function reversedBy()
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    public function billItems()
    {
        return $this->hasMany(BillItem::class, 'bill_id', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'bill_id', 'id');
    }

    //perform selection
    public static function selectBills($id, $bill_reference){
        $bills_query = Bill::with([
            'visit:id,patient_id,stage,open',
            'visit.visitType:id,name',
            'visit.visitClinics.clinic:id,name',
            'reversedBy:id,email',
            'billItems:id,amount,discount,description',
            'transactions:id,transaction_reference,third_party_reference,patient_account_no,hospital_account_no,scheme_name,initiation_time,amount,status,reverse_date'
        ])->whereNull('bills.deleted_by');

        if($id != null){
            $bills_query->where('bills.id', $id);
        }
        elseif($bill_reference != null){
            $bills_query->where('bills.bill_reference_number', $bill_reference);
        }


        else{
            $paginated_bills = $bills_query->paginate(10);

            //return $bills;
            $paginated_bills->getCollection()->transform(function ($bill) {
                return Bill::mapResponse($bill);
            });
    
            return $paginated_bills;
        }


        return $bills_query->get()->map(function ($bill) {
            $bill_details = Bill::mapResponse($bill);

            return $bill_details;
        });


    }

    public static function createBillAndBillItems($request, $visit_id){

        try{
            DB::beginTransaction();

            $created_bill = Bill::create([
                'bill_reference_number'=>Bill::generateUniqueBillReferenceNumber(),
                'visit_id' => $visit_id,
                'initiated_at' => Carbon::now(),
                'bill_amount' => 0.0,
                'discount' => 0.0,
                'status' => APIConstants::STATUS_PENDING,
                'is_reversed' => 0,
                'reason' => $request->reason,
                'created_by' => User::getLoggedInUserId()
            ]);    

            $final_bill_amount = 0.0;
            $final_bill_discount = 0.0;

            //create a dictionary add values to it and access its keys and values
            $service_dictionary = [];
            $service_discount_dictionary = [];
            $service_description_dictionary = [];
            $service_quantity_dictionary = [];
            $amount_to_pay_dictionary = [];
            $service_and_its_service_price_id = [];
    
            foreach($request->service_price_details as $service_price_detail){

                // custom selection.......
                $existing_service_price_details = ServicePrice::selectFirstExactServicePrice($service_price_detail['id'], null, null, null, null, null, null, null,
                    null, null, null, null, null, null, null, null, null, null, null,
                    null, null, null
                );

                //test commit2
                count($existing_service_price_details) < 1 ? throw new NotFoundException(APIConstants::NAME_SERVICE_PRICE) : null;

                !is_numeric($service_price_detail['amount_to_pay']) ? throw new InputsValidationException("Amount to pay must be numeric!!!!") : null;

                // Ensure that item quantity is numeric
                !is_numeric($service_price_detail['quantity']) ? throw new InputsValidationException("Quantity must be numeric!!!!") : null;

                // lets calculate service price selling price and discount
                $selling_price_and_discount_details = Bill::calculateSingleItemSellingPriceAndDiscount(ServicePrice::where('id', $service_price_detail['id'])->get());

                if(isset($service_dictionary[$existing_service_price_details[0]['service']])){
                    if($service_dictionary[$existing_service_price_details[0]['service']] > $selling_price_and_discount_details['selling_price']){
                        $service_dictionary[$existing_service_price_details[0]['service']]  = $selling_price_and_discount_details['selling_price'];
    
                        //setting service price discount
                        $service_discount_dictionary[$existing_service_price_details[0]['service']] = $selling_price_and_discount_details['item_discount'];
    
                        //setting service and its service price id to be used
                        $service_and_its_service_price_id[$existing_service_price_details[0]['service']] = $existing_service_price_details[0]['id'];

                    }
    
                    // increment amount to pay
                    $amount_to_pay_dictionary[$existing_service_price_details[0]['service']] += $service_price_detail['amount_to_pay'];
    
                    // increment service item quantity
                    $service_quantity_dictionary[$existing_service_price_details[0]['service']] += $service_price_detail['quantity'];


                    // concat item description string
                    $service_description_dictionary[$existing_service_price_details[0]['service']] = $service_description_dictionary[$existing_service_price_details[0]['service']] ." ". $service_price_detail['description'];
                }

                else{
                    $service_dictionary[$existing_service_price_details[0]['service']] = $selling_price_and_discount_details['selling_price'] ;

                    //setting service price discount
                    $service_discount_dictionary[$existing_service_price_details[0]['service']] = $selling_price_and_discount_details['item_discount'] ;

                    // increment amount to pay
                    $amount_to_pay_dictionary[$existing_service_price_details[0]['service']] = $service_price_detail['amount_to_pay'];

                    // setting service and its related service price id to be used
                    $service_and_its_service_price_id[$existing_service_price_details[0]['service']] = $existing_service_price_details[0]['id'];

                    // assign service item quantity
                    $service_quantity_dictionary[$existing_service_price_details[0]['service']] = $service_price_detail['quantity'];

                    // assign item description string
                    $service_description_dictionary[$existing_service_price_details[0]['service']] = $service_price_detail['description'];
                }
    
    
            }

            // foreach($request->service_price_details as $service_price_detail){

            //     $existing_service_price_details = ServicePrice::selectFirstExactServicePrice($service_price_detail['id'], null, null, null, null, null, null, null,
            //         null, null, null, null, null, null, null, null, null, null, null,
            //         null, null, null
            //     );

            //     count($existing_service_price_details) < 1 ? throw new NotFoundException(APIConstants::NAME_SERVICE_PRICE) : null;

            //     !is_numeric($service_price_detail['quantity']) ? throw new InputsValidationException("Quantity must be numeric!!!!") : null;
                
    
            //     BillItem::createBillItem(
            //         $created_bill->id
            //         , $existing_service_price_details[0]['id']
            //         , null
            //         ,null
            //         , $amount_to_pay_dictionary[$existing_service_price_details[0]['service']]
            //         , $service_price_detail['discount']
            //         , $service_price_detail['quantity']
            //         , $service_price_detail['description'] ? $service_price_detail['description'] : null
            //     );
    
    
            // }

            // let's save bill item and validate service prices and amount to pay
            $amounts_validation_error = null; //variable to hold amount validation errors if any............

            foreach ($service_dictionary as $key => $value) {
                if (array_key_exists($key, $amount_to_pay_dictionary) && array_key_exists($key, $service_quantity_dictionary) && array_key_exists($key, $service_discount_dictionary) && array_key_exists($key, $service_and_its_service_price_id)  && array_key_exists($key, $service_description_dictionary)) {
                    
                    // save bill item
                    BillItem::createBillItem(
                        $created_bill->id
                        , $service_and_its_service_price_id[$key]
                        , null
                        ,null
                        , $value
                        , $service_discount_dictionary[$key]
                        , $service_quantity_dictionary[$key]
                        , $service_description_dictionary[$key]
                    );
                    
                    if ($value != ($amount_to_pay_dictionary[$key] + $service_discount_dictionary[$key])) {
                        $amounts_validation_error  =  $amounts_validation_error . " Total for service id " .$key . " : ".$value ." is not equal to amount to be paid by patient which is: " .$amount_to_pay_dictionary[$key];
                    } 
                }
            } 

            $amounts_validation_error != null ? throw new InputsValidationException($amounts_validation_error) : null;
            

            Bill::where('id', $created_bill->id)
                    ->update([
                    'bill_amount' => $final_bill_amount,
                    'discount' => $final_bill_discount
                ]);

            //commit transaction if no errors encountered
            DB::commit();
        }

        catch(Exception $e){
            DB::rollBack();
             throw new Exception($e);
        }
        


    }

    public static function calculateTotalBillAndDiscountAmountFromBillItems($bill_id){
        $bill_items = BillItem::where('bill_id', $bill_id)->get();

        $bill_amount_and_discount_details = [
            'bill_amount' => 0.0,
            'bill_discount' => 0.0
        ];
        
        foreach($bill_items as $bill_item){
            $bill_amount_and_discount_details['bill_amount'] += $bill_item->one_item_selling_price * $bill_item->quantity;

            $bill_amount_and_discount_details['bill_discount'] += $bill_item->discount * $bill_item->quantity;
        }

        return $bill_amount_and_discount_details;
    }

    // produces results of a single item item should be directly selected from db not the custom selection.... used for responses purposes only in this case!
    public static function calculateSingleItemSellingPriceAndDiscount($service_price){

        $selling_price_details = [
            'selling_price' => 0.0,
            'item_discount' => 0.0
        ];


        if(isset($service_price[0]['mark_up_type'])){
            $service_price[0]['mark_up_type'] == APIConstants::NAME_PERCENTAGE ? $selling_price_details['selling_price'] = $service_price[0]['cost_price'] * ( 1 + ($service_price[0]['mark_up_value']/100)) : $selling_price_details['selling_price'] = $service_price[0]['cost_price'] + $service_price[0]['mark_up_value'];
        }
        else{
            $selling_price_details['selling_price'] = $service_price[0]['selling_price'];
        }

        // we will use the just set selling price above...
        if(isset($service_price[0]['promotion_type'])){
            $service_price[0]['promotion_type'] == APIConstants::NAME_PERCENTAGE ? $selling_price_details['item_discount'] = $selling_price_details['selling_price'] * ( 1 - ($service_price[0]['promotion_value']/100)) : $selling_price_details['item_discount'] = $selling_price_details['selling_price'] - $service_price[0]['promotion_value'];
        }        

        return $selling_price_details;
    }

    public static function verifyServiceChargeRequest($bill_item){
        Validator::make($bill_item, [
            'service' => 'required|exists:services,name',
            'department' => 'nullable|exists:departments,name',
            'consultation_category' => 'nullable|exists:consultation_categories,name',
            'clinic' => 'nullable|exists:clinics,name',
            'payment_type' => 'nullable|exists:payment_types,name',
            'scheme' => 'nullable|exists:schemes,name',
            'scheme_type' => 'nullable|exists:scheme_types,name',
            'consultation_type' => 'nullable|exists:consultation_types,name',
            'visit_type' => 'nullable|exists:visit_types,name',
            'doctor' => 'nullable|string', // Assuming doctor is an employee
            'lab_test_type' => 'nullable|exists:lab_test_types,name',
            'image_test_type' => 'nullable|exists:image_test_types,name',
            'drug' => 'nullable|exists:drugs,name',
            'brand' => 'nullable|exists:brands,name',
            'branch' => 'nullable|exists:branches,name',
            'building' => 'nullable|exists:buildings,name',
            'wing' => 'nullable|exists:wings,name',
            'ward' => 'nullable|exists:wards,name',
            'office' => 'nullable|exists:offices,name',
            'discount' => 'nullable|numeric', // discount should be numeric
            'current_time' => 'nullable|date_format:H:i', // Valid time in 24-hour format
        
        ]);


    }

     //function to generate unique bill_reference_number
     private static function generateUniqueBillReferenceNumber(){

        // Generate a random six-digit number
        $randomNumber = str_pad(mt_rand(1, 999999999999), 12, '0', STR_PAD_LEFT);

        // Add the B prefix
        $bill_reference_number = 'B' . $randomNumber;

        // Check if the code already exists in the database
        while (Bill::where('bill_reference_number', $bill_reference_number)->exists()) {
            $bill_reference_number = str_pad(mt_rand(1, 999999999999), 12, '0', STR_PAD_LEFT);
            $bill_reference_number = 'B' . $randomNumber;
        }

        return $bill_reference_number;
    }

    private static function mapResponse($bill){
        return [
            'id' => $bill->id,
            'bill_reference_number'=>$bill->bill_reference_number,
            'initiated_at' => $bill->initiated_at,
            'bill_amount' => $bill->bill_amount,
            'discount' => $bill->discount,
            'status' => $bill->status,
            'reason' => $bill->reason,
            'is_reversed' => $bill->is_reversed,
            'reversed_at' => $bill->reversed_at,
            'reversed_by' => $bill->reversedBy ? $bill->reversedBy->email : null,
            'expiry_time' => $bill->expiry_time,
            'visit' => $bill->visit,
            'bill_items' => $bill->billItems,
            'transactions' => $bill->transactions,
            'created_by' => $bill->createdBy ? $bill->createdBy->email : null,
            'created_at' => $bill->created_at,
            'updated_by' => $bill->updatedBy ? $bill->updatedBy->email : null,
            'updated_at' => $bill->updated_at,
            'approved_by' => $bill->approvedBy ? $bill->approvedBy->email : null,
            'approved_at' => $bill->approved_at,    

        ];
    }

    
}
