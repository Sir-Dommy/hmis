<?php

namespace App\Models\Admin\ServiceRelated;

use App\Exceptions\AlreadyExistsException;
use App\Models\Accounts\SubAccounts;
use App\Models\Accounts\Units;
use App\Models\Admin\Brand;
use App\Models\Admin\Clinic;
use App\Models\Admin\ConsultationType;
use App\Models\Admin\Department;
use App\Models\Admin\Drug;
use App\Models\Admin\Employee;
use App\Models\Admin\ImageTestType;
use App\Models\Admin\LabTestType;
use App\Models\Admin\PaymentType;
use App\Models\Admin\Scheme;
use App\Models\Admin\SchemeTypes;
use App\Models\Admin\VisitType;
use App\Models\Branch;
use App\Utils\CustomUserRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePrice extends Model
{
    use HasFactory;

    use CustomUserRelations;

    protected $table = 'service_prices';

    protected $fillable = [
        'service_id',
        'department_id',
        'consultation_category_id',
        'clinic_id',
        'payment_type_id',
        'scheme_id',
        'scheme_type_id',
        'consultation_type_id',
        'visit_type_id',
        'doctor_id',
        'price_applies_from',
        'price_applies_to',
        'duration',
        'lab_test_type_id',
        'image_test_type_id',
        'drug_id',
        'brand_id',
        'branch_id',
        'building_id',
        'wing_id',
        'ward_id',
        'office_id',
        'category',
        'unit_id',
        'smallest_sellable_quantity',
        'cost_price',
        'selling_price',
        'mark_up_type',
        'mark_up_value',
        'promotion_type',
        'promotion_value',
        'income_account_id',
        'asset_account_id',
        'expense_account_id',
        'expiry_date',
        'bar_code',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
        'disabled_by',
        'disabled_at',
        'deleted_by',
        'deleted_at'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function consultationCategory()
    {
        return $this->belongsTo(ConsultationCategory::class, 'consultation_category_id');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }

    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }

    public function schemeType()
    {
        return $this->belongsTo(SchemeTypes::class, 'scheme_type_id');
    }

    public function consultationType()
    {
        return $this->belongsTo(ConsultationType::class, 'consultation_type_id');
    }

    public function visitType()
    {
        return $this->belongsTo(VisitType::class, 'visit_type_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    public function labTestType()
    {
        return $this->belongsTo(LabTestType::class, 'lab_test_type_id');
    }

    public function imageTestType()
    {
        return $this->belongsTo(ImageTestType::class, 'image_test_type_id');
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class, 'drug_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function wing() 
    {
        return $this->belongsTo(Wing::class, 'wing_id');
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }

    public function office()
    {
        return $this->belongsTo(Units::class, 'office_id');
    }

    public function unit()
    {
        return $this->belongsTo(Office::class, 'unit_id');
    }

    public function incomeAccount()
    {
        return $this->belongsTo(SubAccounts::class, 'income_account_id');
    }

    public function assetAccount()
    {
        return $this->belongsTo(SubAccounts::class, 'asset_account_id');
    }

    public function expenseAccount()
    {
        return $this->belongsTo(SubAccounts::class, 'expense_account_id');
    }


    //perform selection
    public static function selectServicePrice($id, $service, $department, $consultation_category, $clinic, $payment_type, $scheme, $scheme_type,
        $consultation_type, $visit_type, $doctor, $price_applies_from, $price_applies_to, $duration, $lab_test_type, $image_test_type, $drug_id, $brand, $branch, $building,
        $wing, $ward, $office
        ){

        $service_prices_query = ServicePrice::with([
            'service:id,name',
            'department:id,name',
            'consultationCategory:id,name',
            'clinic:id,name',
            'paymentType:id,name',
            'scheme:id,name',
            'schemeType:id,name',
            'consultationType:id,name',
            'visitType:id,name',
            'doctor:id,name',
            'labTestType:id,name',
            'imageTestType:id,name',
            'drug:id,name',
            'brand:id,name',
            'branch:id,name',
            'building:id,name',
            'wing:id,name',
            'ward:id,name',
            'office:id,name',
            'unit:id,name',
            'incomeAccount:id,name',
            'assetAccount:id,name',
            'expenseAccount:id,name',
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('service_prices.deleted_by')
          ->whereNull('service_prices.deleted_at');

          // Filter by matching the provided arguments using LIKE for text fields
            if ($service) {
                $service_prices_query->whereHas('service', function ($query) use ($service) {
                    $query->where('name', 'like', "%$service%");
                });
            }

            if ($department) {
                $service_prices_query->whereHas('department', function ($query) use ($department) {
                    $query->where('name', 'like', "%$department%");
                });
            }

            if ($consultation_category) {
                $service_prices_query->whereHas('consultationCategory', function ($query) use ($consultation_category) {
                    $query->where('name', 'like', "%$consultation_category%");
                });
            }

            if ($clinic) {
                $service_prices_query->whereHas('clinic', function ($query) use ($clinic) {
                    $query->where('name', 'like', "%$clinic%");
                });
            }

            if ($payment_type) {
                $service_prices_query->whereHas('paymentType', function ($query) use ($payment_type) {
                    $query->where('name', 'like', "%$payment_type%");
                });
            }

            if ($scheme) {
                $service_prices_query->whereHas('scheme', function ($query) use ($scheme) {
                    $query->where('name', 'like', "%$scheme%");
                });
            }

            if ($scheme_type) {
                $service_prices_query->whereHas('schemeType', function ($query) use ($scheme_type) {
                    $query->where('name', 'like', "%$scheme_type%");
                });
            }

            if ($consultation_type) {
                $service_prices_query->whereHas('consultationType', function ($query) use ($consultation_type) {
                    $query->where('name', 'like', "%$consultation_type%");
                });
            }

            if ($visit_type) {
                $service_prices_query->whereHas('visitType', function ($query) use ($visit_type) {
                    $query->where('name', 'like', "%$visit_type%");
                });
            }

            if ($doctor) {
                $service_prices_query->whereHas('doctor', function ($query) use ($doctor) {
                    $query->where('employee_name', 'like', "%$doctor%")
                            ->orWhere('employee_code', 'like', "%$doctor%")
                            ->orWhere('ipnumber', 'like', "%$doctor%");
                });
            }

            if ($lab_test_type) {
                $service_prices_query->whereHas('labTestType', function ($query) use ($lab_test_type) {
                    $query->where('name', 'like', "%$lab_test_type%");
                });
            }

            if ($image_test_type) {
                $service_prices_query->whereHas('imageTestType', function ($query) use ($image_test_type) {
                    $query->where('name', 'like', "%$image_test_type%");
                });
            }

            if ($drug_id) {
                $service_prices_query->whereHas('drug', function ($query) use ($drug_id) {
                    $query->where('name', 'like', "%$drug_id%");
                });
            }

            if ($brand) {
                $service_prices_query->whereHas('brand', function ($query) use ($brand) {
                    $query->where('name', 'like', "%$brand%");
                });
            }

            if ($branch) {
                $service_prices_query->whereHas('branch', function ($query) use ($branch) {
                    $query->where('name', 'like', "%$branch%");
                });
            }

            if ($building) {
                $service_prices_query->whereHas('building', function ($query) use ($building) {
                    $query->where('name', 'like', "%$building%");
                });
            }

            if ($wing) {
                $service_prices_query->whereHas('wing', function ($query) use ($wing) {
                    $query->where('name', 'like', "%$wing%");
                });
            }

            if ($ward) {
                $service_prices_query->whereHas('ward', function ($query) use ($ward) {
                    $query->where('name', 'like', "%$ward%");
                });
            }

            if ($office) {
                $service_prices_query->whereHas('office', function ($query) use ($office) {
                    $query->where('name', 'like', "%$office%");
                });
            }

            // Filter by 'price_applies_to')
            if ($price_applies_from) {
                $service_prices_query->where(function ($query) use ($price_applies_from) {
                    $query->where('price_applies_from', '=', $price_applies_from);
                });
            }
            // Filter 'price_applies_to')
            if ($price_applies_to) {
                $service_prices_query->where(function ($query) use ($price_applies_to) {
                    $query->where('price_applies_to', '=', $price_applies_to);
                });
            }

            // Filter by matching duration
            if ($duration) {
                $service_prices_query->where('duration', $duration);
            }

        if($id != null){
            $service_prices_query->where('service_prices.id', $id);
        }

        else{
            $paginated_service_prices = $service_prices_query->paginate(10);
            
            $paginated_service_prices->getCollection()->transform(function ($service_price) {
                return ServicePrice::mapResponse($service_price);
            });
    
            return $paginated_service_prices;
        }

        //throw new AlreadyExistsException($service_prices_query->toSql());
        return $service_prices_query->get()->map(function ($service_price) {
            $service_price_details = ServicePrice::mapResponse($service_price);

            return $service_price_details;
        });

    }


    //first exact match service price selection
    public static function selectFirstExactServicePrice($id, $service, $department, $consultation_category, $clinic, $payment_type, $scheme, $scheme_type,
        $consultation_type, $visit_type, $doctor, $current_time, $duration, $lab_test_type, $image_test_type, $drug_id, $brand, $branch, $building,
        $wing, $ward, $office
        ){

        $service_prices_query = ServicePrice::with([
            'service:id,name',
            'department:id,name',
            'consultationCategory:id,name',
            'clinic:id,name',
            'paymentType:id,name',
            'scheme:id,name',
            'schemeType:id,name',
            'consultationType:id,name',
            'visitType:id,name',
            'doctor:id,name',
            'labTestType:id,name',
            'imageTestType:id,name',
            'drug:id,name',
            'brand:id,name',
            'branch:id,name',
            'building:id,name',
            'wing:id,name',
            'ward:id,name',
            'office:id,name',
            'createdBy:id,email',
            'updatedBy:id,email',
            'approvedBy:id,email'
        ])->whereNull('service_prices.deleted_by')
        ->whereNull('service_prices.deleted_at');

        // Filter by matching the provided arguments using LIKE for text fields
            if ($service) {
                $service_prices_query->whereHas('service', function ($query) use ($service) {
                    $query->where('name', $service);
                });
            }

            if ($department) {
                $service_prices_query->whereHas('department', function ($query) use ($department) {
                    $query->where('name', $department);
                });
            }

            if ($consultation_category) {
                $service_prices_query->whereHas('consultationCategory', function ($query) use ($consultation_category) {
                    $query->where('name', $consultation_category);
                });
            }

            if ($clinic) {
                $service_prices_query->whereHas('clinic', function ($query) use ($clinic) {
                    $query->where('name', $clinic);
                });
            }

            if ($payment_type) {
                $service_prices_query->whereHas('paymentType', function ($query) use ($payment_type) {
                    $query->where('name', $payment_type);
                });
            }

            if ($scheme) {
                $service_prices_query->whereHas('scheme', function ($query) use ($scheme) {
                    $query->where('name', $scheme);
                });
            }

            if ($scheme_type) {
                $service_prices_query->whereHas('schemeType', function ($query) use ($scheme_type) {
                    $query->where('name', $scheme_type);
                });
            }

            if ($consultation_type) {
                $service_prices_query->whereHas('consultationType', function ($query) use ($consultation_type) {
                    $query->where('name', $consultation_type);
                });
            }

            if ($visit_type) {
                $service_prices_query->whereHas('visitType', function ($query) use ($visit_type) {
                    $query->where('name', $visit_type);
                });
            }

            if ($doctor) {
                $service_prices_query->whereHas('doctor', function ($query) use ($doctor) {
                    $query->where('employee_code', $doctor)
                            ->orWhere('ipnumber', $doctor);
                });
            }

            if ($lab_test_type) {
                $service_prices_query->whereHas('labTestType', function ($query) use ($lab_test_type) {
                    $query->where('name', $lab_test_type);
                });
            }

            if ($image_test_type) {
                $service_prices_query->whereHas('imageTestType', function ($query) use ($image_test_type) {
                    $query->where('name', $image_test_type);
                });
            }

            if ($drug_id) {
                $service_prices_query->whereHas('drug', function ($query) use ($drug_id) {
                    $query->where('name', $drug_id);
                });
            }

            if ($brand) {
                $service_prices_query->whereHas('brand', function ($query) use ($brand) {
                    $query->where('name', $brand);
                });
            }

            if ($branch) {
                $service_prices_query->whereHas('branch', function ($query) use ($branch) {
                    $query->where('name', $branch);
                });
            }

            if ($building) {
                $service_prices_query->whereHas('building', function ($query) use ($building) {
                    $query->where('name', $building);
                });
            }

            if ($wing) {
                $service_prices_query->whereHas('wing', function ($query) use ($wing) {
                    $query->where('name', $wing);
                });
            }

            if ($ward) {
                $service_prices_query->whereHas('ward', function ($query) use ($ward) {
                    $query->where('name', $ward);
                });
            }

            if ($office) {
                $service_prices_query->whereHas('office', function ($query) use ($office) {
                    $query->where('name', $office);
                });
            }

            // Filter by current time (check if current time is within 'price_applies_from' and 'price_applies_to')
            if ($current_time) {
                $service_prices_query->where(function ($query) use ($current_time) {
                    $query->where('price_applies_from', '<=', $current_time)
                        ->where('price_applies_to', '>=', $current_time);
                });
            }


            // Filter by current time (check if current time is within 'price_applies_from' and 'price_applies_to')
            if ($duration) {
                $service_prices_query->where(function ($query) use ($duration) {
                    $query->where('duration', '<=', $duration);
                });
            }


            if($id != null){
                $service_prices_query->where('service_prices.id', $id);
            }


        //throw new AlreadyExistsException($service_prices_query->toSql());
        
        return $service_prices_query->get()->map(function ($service_price) {
            $service_price_details = ServicePrice::mapResponse($service_price);

            return $service_price_details;
        });

    }


    private static function mapResponse($service_price){
        return [
            'id' => $service_price->id,
            'service' => $service_price->service ? $service_price->service->name : null, // Check if service exists
            'department' => $service_price->department ? $service_price->department->name : null, // Check if department exists
            'consultation_category' => $service_price->consultationCategory ? $service_price->consultationCategory->name : null, // Check if consultationCategory exists
            'clinic' => $service_price->clinic ? $service_price->clinic->name : null, // Check if clinic exists
            'payment_type' => $service_price->paymentType ? $service_price->paymentType->name : null, // Check if paymentType exists
            'scheme' => $service_price->scheme ? $service_price->scheme->name : null, // Check if scheme exists
            'scheme_type' => $service_price->schemeType ? $service_price->schemeType->name : null, // Check if schemeType exists
            'consultation_type' => $service_price->consultationType ? $service_price->consultationType->name : null, // Check if consultationType exists
            'visit_type' => $service_price->visitType ? $service_price->visitType->name : null, // Check if visitType exists
            'doctor_name' => $service_price->doctor ? $service_price->doctor->employee_name : null, // Check if doctor exists
            'doctor_ipnumber' => $service_price->doctor ? $service_price->doctor->ipnumber : null,
            'doctor_employee_code' => $service_price->doctor ? $service_price->doctor->employee_code : null,
            'lab_test_type' => $service_price->labTestType ? $service_price->labTestType->name : null, // Check if labTestType exists
            'image_test_type' => $service_price->imageTestType ? $service_price->imageTestType->name : null, // Check if imageTestType exists
            'drug' => $service_price->drug ? $service_price->drug->name : null, // Check if drug exists
            'brand' => $service_price->brand ? $service_price->brand->name : null, // Check if brand exists
            'branch' => $service_price->branch ? $service_price->branch->name : null, // Check if branch exists
            'building' => $service_price->building ? $service_price->building->name : null, // Check if building exists
            'wing' => $service_price->wing ? $service_price->wing->name : null, // Check if wing exists
            'ward' => $service_price->ward ? $service_price->ward->name : null, // Check if ward exists
            'office' => $service_price->office ? $service_price->office->name : null, // Check if office exists
            'price' => ServicePrice::calculatePrice($service_price), // Service price
            'price_applies_from' => $service_price->price_applies_from, // Price applies from date
            'price_applies_to' => $service_price->price_applies_to, // Price applies to date
            'duration' => $service_price->duration, // Duration
            'created_by' => $service_price->createdBy ? $service_price->createdBy->email : null, // Created by user email
            'created_at' => $service_price->created_at, // Created at timestamp
            'updated_by' => $service_price->updatedBy ? $service_price->updatedBy->email : null, // Updated by user email
            'updated_at' => $service_price->updated_at, // Updated at timestamp
            'approved_by' => $service_price->approvedBy ? $service_price->approvedBy->email : null, // Approved by user email
            'approved_at' => $service_price->approved_at, // Approved at timestamp
        ];
    }

    private static function calculatePrice($service_price){
        $cost_price = $service_price->cost_price;
        $selling_price = $service_price->selling_price;
        $mark_up_type = $service_price->mark_up_type;
        $mark_up_value = $service_price->mark_up_value;
        $promotion_type = $service_price->promotion_type;
        $promotion_value = $service_price->promotion_value;

        $return_price = $cost_price;

        if($mark_up_type == "Percentage"){
            $return_price =  $cost_price * (100 + ($mark_up_value/100));
            throw new AlreadyExistsException($return_price);
        }

        else if($mark_up_type == "Fixed"){
            $return_price += $mark_up_value;
        }

        //subtract promotions
        if($promotion_type == "Percentage"){
            $return_price -=  $return_price * $promotion_type/100;
        }

        else if($promotion_type == "Fixed"){
            $return_price -= $promotion_value;
        }

        return $return_price;
    }


}
