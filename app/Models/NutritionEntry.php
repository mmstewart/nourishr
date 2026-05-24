<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

#[Fillable(['calories', 'carbohydrates', 'date', 'fats', 'fdc_id', 'food_item', 'meal_type', 'protein', 'quantity', 'serving_size', 'user_id'])]
class NutritionEntry extends Model
{
    use HasFactory;

    public function casts()
    {
        return [
            'quantity' => 'float',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getOpenFoodFactsServingOptions(string $barcode)
    {
        $response = Http::get("https://world.openfoodfacts.org/api/v0/product/{$barcode}.json")->json();
        $product = $response['product'] ?? [];

        return [
            ['label' => '100g', 'gramWeight' => 100],
            ...($product['serving_size'] ? [[
                'label' => $product['serving_size'],
                'gramWeight' => $product['serving_quantity'] ?? 100,
            ]] : []),
        ];
    }

    public function getUSDAServingOptions(string $fdcId)
    {
        $response = Http::get("https://api.nal.usda.gov/fdc/v1/food/{$fdcId}", [
            'api_key' => config('services.usda.key'),
        ])->json();

        $options = [['label' => '100g', 'gramWeight' => 100]];

        foreach ($response['foodPortions'] ?? [] as $portion) {
            $options[] = [
                'label' => strtolower($portion['portionDescription']),
                'gramWeight' => $portion['gramWeight'],
            ];
        }

        foreach ($response['foodMeasures'] ?? [] as $measure) {
            $options[] = [
                'label' => $measure['disseminationText'],
                'gramWeight' => $measure['gramWeight'],
            ];
        }

        if (isset($response['servingSize'], $response['householdServingFullText'])) {
            $options[] = [
                'label' => strtolower($response['householdServingFullText']),
                'gramWeight' => $response['servingSize'],
            ];
        }

        return $options;
    }

    public function getServingOptions()
    {
        return $this->fdc_id
            ? $this->getUSDAServingOptions($this->fdc_id)
            : $this->getOpenFoodFactsServingOptions($this->barcode);
    }
}
