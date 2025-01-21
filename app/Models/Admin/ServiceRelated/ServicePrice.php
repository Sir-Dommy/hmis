<?php

namespace App\Models\Admin\ServiceRelated;

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
        'price',
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
        return $this->belongsTo(Office::class, 'office_id');
    }

}
