<?php

namespace App\Http\Controllers\CV;

use Illuminate\Http\JsonResponse;

class ValidationController
{
    /**
     * Check for future dates in an array of items.
     *
     * @param array|null $items
     * @param string $fieldName
     * @return true|JsonResponse
     */
    public static function futureDateCheck(?array $items, string $fieldName = 'date_completed')
    {
        if (empty($items) || !is_array($items)) {
            return true; // nothing to check
        }

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
