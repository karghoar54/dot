<?php
// app/Http/Controllers/Api/DotController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FMCSDot;
use App\Models\DotDetail;
use App\Models\FMCSAInspection;
use App\Http\Responses\ApiResponse;

class DotController extends Controller
{
    use ApiResponse;

    /**
     * List DOTs
     *
     * Get a paginated list of DOTs with filters.
     *
     * @header Accept-Language en
     * @queryParam per_page int Number of results per page. Maximum: 100. Example: 10
     * @queryParam dot int DOT number. Example: 123456
     * @queryParam ein string EIN. Example: 12-3456789
     * @queryParam vin string VIN. Example: 1HGCM82633A004352
     * @queryParam license string License. Example: ABC123
     * @queryParam mc string MC. Example: MC123456
     * @queryParam company_name string Company Name. Example: Acme Inc
     * @queryParam company_owner string Company Owner. Example: John Doe
     * @queryParam dba string DBA. Example: Acme Logistics
     * @queryParam city string City. Example: Miami
     * @queryParam state string State. Example: FL
     * @queryParam inspections string Inspection type. Example: Safety
     * @response 200 {"success":true,"message":"DOTs list retrieved successfully.","data":{}}
     */
    // GET /api/dots
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1',
            'dot' => 'sometimes|numeric',
            'ein' => 'sometimes|string|max:32',
            'vin' => 'sometimes|string|max:32',
            'license' => 'sometimes|string|max:32',
            'mc' => 'sometimes|string|max:32',
            'company_name' => 'sometimes|string|max:255',
            'company_owner' => 'sometimes|string|max:255',
            'dba' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:64',
            'state' => 'sometimes|string|max:32',
            'inspections' => 'sometimes|string|max:64',
        ], [
            'per_page.integer' => __('messages.invalid_per_page'),
            'per_page.min' => __('messages.invalid_per_page'),
            'dot.numeric' => __('messages.invalid_dotnumber'),
            'ein.string' => __('messages.validation_failed'),
            'vin.string' => __('messages.validation_failed'),
            'license.string' => __('messages.validation_failed'),
            'mc.string' => __('messages.validation_failed'),
            'company_name.string' => __('messages.validation_failed'),
            'company_owner.string' => __('messages.validation_failed'),
            'dba.string' => __('messages.validation_failed'),
            'city.string' => __('messages.validation_failed'),
            'state.string' => __('messages.validation_failed'),
            'inspections.string' => __('messages.validation_failed'),
        ]);
        // Filtros: EIN, VIN, License, DOT, MC, Company Name, Company Owner, DBA, City, State, Inspections
        $query = FMCSDot::query();
        $filters = [
            'ein' => 'ein',
            // 'vin' => 'VIN', // VIN está en FMCSAInspections, no en FMCSADots
            // 'license' => 'License', // License está en FMCSAInspections
            'dot' => 'dotNumber',
            // 'mc' => 'icc1', // Si corresponde, revisar si existe en FMCSADots
            'company_name' => 'legalName',
            // 'company_owner' => '', // No existe en FMCSADots
            'dba' => 'dbaName',
            'city' => 'phyCity',
            'state' => 'phyState',
        ];
        foreach ($filters as $param => $column) {
            if ($request->filled($param)) {
                $query->where($column, 'like', '%' . $request->input($param) . '%');
            }
        }
        // Filtros especiales para VIN y License en Inspections
        if ($request->filled('vin')) {
            $query->whereHas('inspections', function ($q) use ($request) {
                $q->where('VIN', 'like', '%' . $request->input('vin') . '%');
            });
        }
        if ($request->filled('license')) {
            $query->whereHas('inspections', function ($q) use ($request) {
                $q->where('UNIT_LICENSE', 'like', '%' . $request->input('license') . '%');
            });
        }
        $maxPerPage = 100; // Set your desired maximum
        $perPage = min($request->input('per_page', 15), $maxPerPage);
        $dots = $query->paginate($perPage);
        return $this->successResponse($dots, __('messages.dots_list'));
    }

    /**
     * Show DOT detail
     *
     * Get detail for a specific DOT number.
     *
     * @header Accept-Language en
     * @urlParam dotnumber int required DOT number. Example: 123456
     * @response 200 {"success":true,"message":"DOT detail retrieved successfully.","data":{}}
     * @response 404 {"success":false,"message":"DOT not found.","data":null}
     */
    // GET /api/dots/{dotnumber}
    public function show($dotnumber)
    {
        if (!is_numeric($dotnumber)) {
            return $this->errorResponse(__('messages.invalid_dotnumber'), 422);
        }
        $dot = FMCSDot::where('dotNumber', $dotnumber)->first();
        if (!$dot) {
            return $this->errorResponse(__('messages.dot_not_found'), 404);
        }
        $details = DotDetail::where('dot_number', $dotnumber)->get();
        $inspections = FMCSAInspection::where('DOT_NUMBER', $dotnumber)->get();
        return $this->successResponse([
            'dot' => $dot,
            'details' => $details,
            'inspections' => $inspections
        ], __('messages.dot_detail'));
    }
}
