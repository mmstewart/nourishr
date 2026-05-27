<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Exception;

class FoodFactsController extends Controller
{
    public function __invoke() 
    {
        $food_item = request()->query('food_item');
        $type = request()->query('type', 'search');

        if (!$food_item) {
            return response()->json(['error' => 'Missing food item'], 400);
        }

        try {
            if ($type === 'scan') {
                $response = Http::withHeaders([
                    'User-Agent' => 'Nourishr/1.0 (Nourishr)',
                ])->get("https://world.openfoodfacts.org/api/v2/product/{$food_item}", [
                    'fields' => 'product_name,nutriments',
                ]);

                $data = $response->json();

                if (($data['status'] ?? 0) === 1) {
                    return response()->json($data['product']);
                }

                return response()->json([], 404);
            }

            $response = Http::get('https://api.nal.usda.gov/fdc/v1/foods/search', [
                'query'    => $food_item,
                'pageSize' => 20,
                'api_key'  => env('USDA_API_SECRET'),
            ]);

            return response()->json($response->json());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}