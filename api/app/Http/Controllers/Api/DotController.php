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
     * @queryParam per_page int Number of results per page. Example: 10
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
            'ein' => 'EIN',
            'vin' => 'VIN',
            'license' => 'License',
            'dot' => 'DOT',
            'mc' => 'MC',
            'company_name' => 'CompanyName',
            'company_owner' => 'CompanyOwner',
            'dba' => 'DBA',
            'city' => 'City',
            'state' => 'State',
        ];
        foreach ($filters as $param => $column) {
            if ($request->filled($param)) {
                $query->where($column, 'like', '%' . $request->input($param) . '%');
            }
        }
        // Inspections: filtro especial
        if ($request->filled('inspections')) {
            $query->whereHas('inspections', function ($q) use ($request) {
                $q->where('InspectionType', 'like', '%' . $request->input('inspections') . '%');
            });
        }
        $perPage = $request->input('per_page', 15);
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
        $dot = FMCSDot::where('DOT', $dotnumber)->first();
        if (!$dot) {
            return $this->errorResponse(__('messages.dot_not_found'), 404);
        }
        $details = DotDetail::where('DOT', $dotnumber)->get();
        $inspections = FMCSAInspection::where('DOT', $dotnumber)->get();
        return $this->successResponse([
            'dot' => $dot,
            'details' => $details,
            'inspections' => $inspections
        ], __('messages.dot_detail'));
    }
}
