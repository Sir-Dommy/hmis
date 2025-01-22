<?php

namespace App\Models\Bill;

use App\Models\Admin\ServiceRelated\Service;
use App\Models\Admin\ServiceRelated\ServicePrice;
use App\Models\Patient\Visit;
use App\Models\User;
use App\Utils\CustomUserRelations;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public static function createBill($request){

        //verify request first
        Bill::verifyServiceChargeRequest($request);

        $existing_service = Service::selectServices(null, $request->service);

        $existing_service[0]['service_price_affected_by_time'] ? $current_time = Carbon::now()->format('H:i') : $current_time = null;

        $service_price_and_details = ServicePrice::selectFirstExactServicePrice(null, $request->service, $request->department, $request->consultation_category, $request->clinic, $request->payment_type, $request->scheme, $request->scheme_type,
            $request->consultation_type, $request->visit_type, $request->doctor, $current_time, $request->duration, $request->lab_test_type, $request->image_test_type, $request->drug_id, $request->brand, $request->branch, $request->building,
            $request->wing, $request->ward, $request->office
        );

    }

    public static function verifyServiceChargeRequest($request){
        $request->validate([
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
            'price' => 'required|numeric', // Price should be numeric
            'current_time' => 'nullable|date_format:H:i', // Valid time in 24-hour format
        ]);
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
            'bill_items' => $bill->transactions,
            'created_by' => $bill->createdBy ? $bill->createdBy->email : null,
            'created_at' => $bill->created_at,
            'updated_by' => $bill->updatedBy ? $bill->updatedBy->email : null,
            'updated_at' => $bill->updated_at,
            'approved_by' => $bill->approvedBy ? $bill->approvedBy->email : null,
            'approved_at' => $bill->approved_at,    

        ];
    }
}
