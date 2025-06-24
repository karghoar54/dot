<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KarghoOnboardingController extends Controller
{
    /**
     * Onboard a DOT from FMCSA into KarghoUS
     *
     * This endpoint triggers the onboarding process for a DOT number from FMCSA, using the provided password and country ID. The DOT number is passed as a URL parameter, and the password and country ID are sent in the request body. The process will call a stored procedure in the KarghoUS database. Returns a success message if onboarding is successful, or an error message if validation fails or the DOT is not found.
     *
     * @header Accept-Language en
     * @urlParam dotnumber string required DOT number. Example: 123456
     * @bodyParam password string required Password. Example: 123456
     * @bodyParam idcountry int required Country ID (1=USA, 2=ARG). Example: 1
     * @response 200 {"success":true,"message":"Onboarding executed successfully."}
     * @response 400 {"success":false,"message":"Validation error."}
     * @response 404 {"success":false,"message":"DOT not found in FMCSA."}
     */
    public function onboardFromFMCSA(Request $request, $dotnumber)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|max:100',
            'idcountry' => 'required|integer|in:1,2',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => $validator->errors()
            ], 400);
        }
        $dot = $dotnumber;
        $password = bcrypt($request->input('password'));
        $idcountry = $request->input('idcountry');
        try {
            //TODO: recordar agregar indices:
            // - CREATE NONCLUSTERED INDEX IX_FMCSAInspections_DotNumber_VIN ON FMCSA.dbo.FMCSAInspections(dot_number, VIN);
            // - CREATE NONCLUSTERED INDEX IX_DOTsDetail_DotNumber ON FMCSA.dbo.DOTsDetail(dot_number);
            // - CREATE NONCLUSTERED INDEX IX_FMCSAInspections_DotNumber_VIN ON FMCSA.dbo.FMCSAInspections(dot_number, VIN);
            // - CREATE NONCLUSTERED INDEX IX_FMCSAInspections_UniqueId ON FMCSA.dbo.FMCSAInspections(unique_id);
            DB::connection('karghous')->statement(
                "EXEC OnBoarding @DOT = ?, @PasswordEncrypted = ?, @IdCountry = ?",
                [$dot, $password, $idcountry]
            );
            return response()->json([
                'success' => true,
                'message' => __('messages.onboarding_success'),
            ]);
        } catch (\Exception $e) {
            // Extract only the SQL error message, not the query or connection details
            $errorMsg = $e->getMessage();
            // Try to extract the SQL Server error message (after the last ']')
            if (preg_match('/\[SQL Server\](.*?)(\(|$)/', $errorMsg, $matches)) {
                $cleanMsg = trim($matches[1]);
            } else {
                // Fallback: remove SQLSTATE and connection info
                $cleanMsg = preg_replace('/SQLSTATE\[[^\]]*\]:? ?/', '', $errorMsg);
                $cleanMsg = preg_replace('/\(Connection:.*$/', '', $cleanMsg);
                $cleanMsg = trim($cleanMsg);
            }
            return response()->json([
                'success' => false,
                'message' => __('messages.onboarding_error'),
                'error' => $cleanMsg,
            ], 500);
        }
    }
    /**
     * Offboard a DOT from KarghoUS
     *
     * This endpoint triggers the offboarding process for a DOT number from FMCSA, removing or deactivating it in the KarghoUS database. The DOT number is passed as a URL parameter. Returns a success message if offboarding is successful, or an error message if the DOT is not found or the operation fails.
     *
     * @header Accept-Language en
     * @urlParam dotnumber string required DOT number. Example: 123456
     * @response 200 {"success":true,"message":"Offboarding executed successfully."}
     * @response 404 {"success":false,"message":"DOT not found in KarghoUS."}
     * @response 500 {"success":false,"message":"Error executing offboarding.","error":"..."}
     *
     * @method DELETE
     */
    public function offboardFromFMCSA(Request $request, $dotnumber)
    {
        if (empty($dotnumber)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => ['dotnumber' => ['The DOT number is required.']]
            ], 400);
        }
        try {
            DB::connection('karghous')->statement(
                "EXEC OffBoarding @DOT = ?",
                [$dotnumber]
            );
            return response()->json([
                'success' => true,
                'message' => __('messages.offboarding_success'),
            ]);
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            if (preg_match('/\[SQL Server\](.*?)(\(|$)/', $errorMsg, $matches)) {
                $cleanMsg = trim($matches[1]);
            } else {
                $cleanMsg = preg_replace('/SQLSTATE\[[^\]]*\]:? ?/', '', $errorMsg);
                $cleanMsg = preg_replace('/\(Connection:.*$/', '', $cleanMsg);
                $cleanMsg = trim($cleanMsg);
            }
            return response()->json([
                'success' => false,
                'message' => __('messages.offboarding_error'),
                'error' => $cleanMsg,
            ], 500);
        }
    }
}
