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



    public const MESSAGE_ALREADY_EXISTS = 'Already exists';
    public const MESSAGE_NOT_FOUND = 'Not found';
    public const MESSAGE_MISSING_OR_INVALID_INPUTS = 'Missing or ivalid inputs';



    public const NAME_CREATE = 'CREATE';
    public const NAME_UPDATE = 'UPDATE';
    public const NAME_GET = 'GET';
    public const NAME_APPROVE = 'APPROVE';
    public const NAME_DISABLE = 'DISABLE';
    public const NAME_SOFT_DELETE = 'SOFT_DELETE';
    public const NAME_PERMANENT_DELETE = 'PERMANENT_DELETE';


    public const NAME_DEPARTMENT = 'Department';
    public const NAME_EMPLOYEE = 'Employee';
    public const NAME_SCHEME = 'Scheme';
    public const NAME_PATIENT = 'Patient';
    public const NAME_PAYMENT_TYPE = 'Payment type';
    public const NAME_CLINIC = 'Clinic';
    public const NAME_VISIT = 'Visit';
}