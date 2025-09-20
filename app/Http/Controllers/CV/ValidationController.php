<?php

namespace App\Http\Controllers\CV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ValidationController extends Controller
{
     /**
     * Check if any date in the array is in the future.
     *
     * @param array $items
     * @param string $fieldName
     * @return JsonResponse|bool
     */

    public static function futureDateCheck(array $items, string $fieldName = 'date_completed')
    {
        foreach ($items as $item) {
            if (!empty($item[$fieldName]) && strtotime($item[$fieldName]) > strtotime(date('Y-m-d'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'The completion date cannot be in the future.',
                ], 422);
            }
        }
        return true;
    }



}
