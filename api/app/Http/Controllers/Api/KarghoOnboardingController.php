<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KarghoOnboardingController extends Controller
{
    /**
     * Trigger onboarding using a DOT number from FMCSA.
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
        $validator = Validator::make(array_merge($request->all(), ['dot' => $dotnumber]), [
            'dot' => 'required|string|max:50',
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
            DB::connection('karghous')->statement(
                "EXEC OnBoarding @DOT = ?, @PasswordEncrypted = ?, @IdCountry = ?",
                [$dot, $password, $idcountry]
            );
            return response()->json([
                'success' => true,
                'message' => __('messages.onboarding_success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.onboarding_error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
