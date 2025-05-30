<?php 

namespace App\Utils;

class APIConstants
{
    // Success messages
    public const SUCCESS_MESSAGE = 'Operation completed successfully';
    public const RESOURCE_CREATED = 'Resource has been created successfully';


    // Error messages
    public const INVALID_REQUEST = 'Invalid request data provided';
    public const RESOURCE_NOT_FOUND = 'Requested resource not found';
    public const ROUTE_NOT_FOUND = 'Route not found';
    public const UNAUTHORIZED_ACCESS = 'You are not authorized to perform this action';
    public const ACCESS_DENIED = 'Access Denied';
    public const VALIDATION_ERROR = 'Validation error';
    public const METHOD_NOT_ALLOWED = 'Method not allowed';
    public const SERVER_ERROR = 'An unexpected error occurred. Please try again later';



    public const MESSAGE_ALREADY_EXISTS = 'With similar details already exists';
    public const MESSAGE_NOT_FOUND = 'Not found';
    public const MESSAGE_MISSING_OR_INVALID_INPUTS = 'Missing or invalid inputs';



    public const NAME_CREATE = 'CREATE';
    public const NAME_UPDATE = 'UPDATE';
    public const NAME_GET = 'GET';
    public const NAME_APPROVE = 'APPROVE';
    public const NAME_DISABLE = 'DISABLE';
    public const NAME_SOFT_DELETE = 'SOFT_DELETE';
    public const NAME_RESTORE = 'Restore';
    public const NAME_PERMANENT_DELETE = 'PERMANENT_DELETE';


    public const NAME_DEPARTMENT = 'Department';
    public const NAME_EMPLOYEE = 'Employee';
    public const NAME_SCHEME = 'Scheme';
    public const NAME_PATIENT = 'Patient';
    public const NAME_PAYMENT_TYPE = 'Payment type';
    public const NAME_PAYMENT_PATH = 'Payment path';
    public const NAME_CLINIC = 'Clinic';
    public const NAME_VISIT = 'Visit';
    public const NAME_VITAL = 'Vital';
    public const NAME_EMERGENCY_VISIT = 'Emergency visit';
    public const NAME_PHYSICAL_EXAMINATION_TYPE = 'Physical Examination Type';
    public const NAME_DIAGNOSIS= 'Diagnosis';
    public const NAME_CONSULTATION_TYPE = 'Consultation type';
    public const NAME_SYMPTOM = 'Symptom';
    public const NAME_CHRONIC_DISEASE = 'Chronic disease';
    public const NAME_LAB_TEST_TYPE = 'Lab test type';
    public const NAME_LAB_TEST_CLASS = 'Lab test class';
    public const NAME_LAB_TEST_REQUEST = 'Lab test request';
    public const NAME_IMAGE_TEST_REQUEST = 'Image test request';
    public const NAME_IMAGE_TEST_CLASS = 'Image test class';
    public const NAME_IMAGE_TEST_TYPE = 'Image test type';
    public const NAME_BRAND = 'Brand';
    public const NAME_DRUG_FORMULATION = 'Drug formulation';
    public const NAME_DRUG = 'Drug';
    public const NAME_SERVICE = 'Service';
    public const NAME_BILL = 'Bill';
    public const NAME_BILL_ITEM = 'Bill item';
    public const NAME_BILL_CHANGE_REQUEST = 'Bill change request';
    public const NAME_BILL_ITEM_CHANGE_REQUEST = 'Bill item Change request';
    public const NAME_TRANSACTION = 'Transaction';
    public const NAME_TRANSACTION_CHANGE_REQUEST = 'Transaction change request';
    public const NAME_BUILDING = 'Building';
    public const NAME_WING = 'Wing';
    public const NAME_WARD = 'Ward';
    public const NAME_OFFICE = 'Office';
    public const NAME_SERVICE_PRICE = 'Service price';
    public const NAME_BRANCH = 'Branch';
    public const NAME_MAIN_ACCOUNT = 'Main account';
    public const NAME_SUB_ACCOUNT = 'Sub account';
    public const NAME_UNIT = 'Unit';
    public const NAME_PRESCRIPTION = 'Prescription';
    public const NAME_CASH = 'Cash';
    public const NAME_INSURANCE = 'Insurance';
    public const NAME_NURSE_REPORT = 'Nurse report';


    //mark up and discount calculation types
    public const NAME_PERCENTAGE = 'Percentage';
    public const NAME_FIXED = 'Fixed';


    // Bill and transactions statuses
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_PENDING_CODE = '000';
    public const STATUS_SUCCESS = 'SUCCESS';
    public const STATUS_SUCCESS_CODE = '001';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_REJECTED_CODE = '003';
    public const STATUS_CANCELLED = 'CANCELLED';
    public const STATUS_CANCELLED_CODE = '008';
    public const STATUS_REVERSED = 'REVERSED';
    public const STATUS_REVERSED_CODE = '009';
    public const STATUS_PENDING_REVERSAL = 'PENDING REVERSAL';
    public const STATUS_PENDING_REVERSAL_CODE = '0010';


    // visit stages
    public const DEFAULT_STAGE = 'Default';
    public const FRONT_OFFICE_STAGE = 'Front office';
    public const LAB_STAGE = 'Lab';
    public const BILLING_STAGE = 'Billing';
    public const ADMITTED_STAGE = 'Admitted';
    public const CONSULTING_STAGE = 'Consulting';
    public const TRIAGE_STAGE = 'Triage';

}