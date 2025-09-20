<?php

namespace App\Http\Controllers\CV;

use Illuminate\Http\JsonResponse;

class ValidationController
{
    /**
     * Check if any date field in the array is in the future.
     *
     * @param array|null $items
     * @param string $field
     * @return true|JsonResponse
     */
    public static function futureDateCheck(?array $items, string $field = 'date_completed')
    {
        if (empty($items) || !is_array($items)) {
            return true; // nothing to validate
        }

        foreach ($items as $item) {
            if (isset($item[$field]) && strtotime($item[$field]) > strtotime(date('Y-m-d'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'The completion date cannot be in the future.',
                ], 422);
            }
        }

        return true; // all dates are valid
    }

    public static function futureDateCheckArray(array $items, string $field)
    {
        foreach ($items as $item) {
            if (isset($item[$field]) && strtotime($item[$field]) > time()) {
                return response()->json([
                    'success' => false,
                    'message' => "$field cannot be a future date."
                ], 422);
            }
        }
        return true;
    }

}
