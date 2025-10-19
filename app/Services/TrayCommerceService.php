<?php

namespace App\Services;

use App\Models\TrayCredential;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TrayCommerceService
{
    protected $credentials;

    public function __construct()
    {
        $this->credentials = TrayCredential::first();
    }

    public function generateTokens()
    {
        if (!$this->credentials) {
            throw new \Exception('Tray credentials not configured.');
        }

        $response = Http::asForm()->post($this->credentials->api_host, [
            'consumer_key' => $this->credentials->consumer_key,
            'consumer_secret' => $this->credentials->consumer_secret,
            'code' => $this->credentials->code,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            $this->credentials->update([
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'date_expiration_access_token' => $data['date_expiration_access_token'],
                'date_expiration_refresh_token' => $data['date_expiration_refresh_token'],
                'date_activated' => $data['date_activated'],
            ]);

            return $data;
        }

        throw new \Exception('Failed to generate Tray tokens: ' . $response->body());
    }

    public function refreshToken()
    {
        if (!$this->credentials || !$this->credentials->refresh_token) {
            throw new \Exception('Tray refresh token not available.');
        }

        $response = Http::get($this->credentials->api_host . '/auth', [
            'refresh_token' => $this->credentials->refresh_token,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $this->updateTokens($data);
            return $data['access_token'];
        }

        throw new \Exception('Failed to refresh Tray token: ' . $response->body());
    }

    protected function getAccessToken()
    {
        if (!$this->credentials || !$this->credentials->access_token) {
            throw new \Exception('Tray access token not available. Please generate tokens first.');
        }

        if (Carbon::parse($this->credentials->date_expiration_access_token)->isPast()) {
            return $this->refreshToken();
        }

        return $this->credentials->access_token;
    }

    protected function updateTokens($data)
    {
        $this->credentials->update([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'date_expiration_access_token' => $data['date_expiration_access_token'],
            'date_expiration_refresh_token' => $data['date_expiration_refresh_token'],
            'date_activated' => $data['date_activated'],
        ]);
    }

    public function sendCategory(\App\Models\Category $category)
    {
        $accessToken = $this->getAccessToken();
        $url = $this->credentials->api_host . '/categories';
        $data = [
            'Category' => [
                'name' => $category->name,
                'description' => $category->description,
                'slug' => $category->slug,
            ]
        ];

        if ($category->tray_id) {
            // Update existing category
            $response = Http::put($url . '/' . $category->tray_id, [
                'access_token' => $accessToken,
                'Category' => $data['Category'],
            ]);
        } else {
            // Create new category
            $response = Http::post($url, [
                'access_token' => $accessToken,
                'Category' => $data['Category'],
            ]);
        }

        if ($response->successful()) {
            $responseData = $response->json();
            if (isset($responseData['id'])) {
                $category->update(['tray_id' => $responseData['id']]);
            }
            return $responseData;
        }

        throw new \Exception('Failed to send category to Tray: ' . $response->body());
    }

    public function sendBrand(\App\Models\Brand $brand)
    {
        $accessToken = $this->getAccessToken();
        $url = $this->credentials->api_host . '/products/brands';
        $data = [
            'Brand' => [
                'brand' => $brand->brand,
                'slug' => Str::slug($brand->brand),
            ]
        ];

        if ($brand->tray_id) {
            // Update existing brand
            $response = Http::put($url . '/' . $brand->tray_id, [
                'access_token' => $accessToken,
                'Brand' => $data['Brand'],
            ]);
        } else {
            // Create new brand
            $response = Http::post($url, [
                'access_token' => $accessToken,
                'Brand' => $data['Brand'],
            ]);
        }

        if ($response->successful()) {
            $responseData = $response->json();
            if (isset($responseData['id'])) {
                $brand->update(['tray_id' => $responseData['id']]);
            }
            return $responseData;
        }

        throw new \Exception('Failed to send brand to Tray: ' . $response->body());
    }

    public function sendProduct(\App\Models\Product $product)
    {
        if (empty($product->category->tray_id)) {
            throw new \Exception('Category not synced to Tray. Please sync the category first.');
        }

        if (empty($product->brand->tray_id)) {
            throw new \Exception('Brand not synced to Tray. Please sync the brand first.');
        }

        $accessToken = $this->getAccessToken();
        $url = $this->credentials->api_host . '/products';
        $data = [
            'Product' => [
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'ean' => $product->ean,
                'ncm' => $product->ncm,
                'category_id' => $product->category->tray_id,
                'brand' => $product->brand->brand,
                'stock' => 100, // Default stock
                'available' => 1,
            ]
        ];

        if ($product->tray_id) {
            // Update existing product
            $response = Http::put($url . '/' . $product->tray_id, [
                'access_token' => $accessToken,
                'Product' => $data['Product'],
            ]);
        } else {
            // Create new product
            $response = Http::post($url, [
                'access_token' => $accessToken,
                'Product' => $data['Product'],
            ]);
        }

        if ($response->successful()) {
            $responseData = $response->json();
            if (isset($responseData['id'])) {
                $product->update(['tray_id' => $responseData['id']]);
            }
            return $responseData;
        }

        throw new \Exception('Failed to send product to Tray: ' . $response->body());
    }

    public function sendVariant(\App\Models\Variant $variant)
    {
        if (empty($variant->product->tray_id)) {
            throw new \Exception('Parent product is not synced to Tray. Please sync the product first.');
        }

        $accessToken = $this->getAccessToken();
        $url = $this->credentials->api_host . '/products/variants';

        $data = [
            'Variant' => [
                'product_id' => $variant->product->tray_id,
                'price' => $variant->price ?? $variant->product->price,
                'stock' => $variant->stock,
                'Sku' => [
                    ['type' => $variant->type, 'value' => $variant->value]
                ]
            ]
        ];

        if ($variant->tray_id) {
            // Update existing variant
            $data['Variant']['id'] = $variant->tray_id;
            $response = Http::put($url . '/' . $variant->tray_id, [
                'access_token' => $accessToken,
                'Variant' => $data['Variant'],
            ]);
        } else {
            // Create new variant
            $response = Http::post($url, [
                'access_token' => $accessToken,
                'Variant' => $data['Variant'],
            ]);
        }

        if ($response->successful()) {
            $responseData = $response->json();
            if (isset($responseData['id'])) {
                $variant->update(['tray_id' => $responseData['id']]);
            }
            return $responseData;
        }

        throw new \Exception('Failed to send variant to Tray: ' . $response->body());
    }

    public function sendImage(\App\Models\Product $product)
    {
        if (empty($product->tray_id)) {
            throw new \Exception('Product is not synced to Tray. Please sync the product first.');
        }

        if (empty($product->image)) {
            throw new \Exception('Product does not have an image to sync.');
        }

        $accessToken = $this->getAccessToken();
        $url = $this->credentials->api_host . '/products/' . $product->tray_id . '/images';

        $data = [
            'access_token' => $accessToken,
            'Images' => [
                'picture_source_1' => asset('storage/' . $product->image),
            ]
        ];

        $response = Http::post($url, $data);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Failed to send image to Tray: ' . $response->body());
    }

    public function sendProperty(\App\Models\Property $property)
    {
        $accessToken = $this->getAccessToken();
        $url = $this->credentials->api_host . '/properties';

        $values = $property->values->map(function ($value) {
            return ['name' => $value->name];
        })->toArray();

        $data = [
            'access_token' => $accessToken,
            'name' => $property->name,
            'PropertyValues' => $values,
        ];

        if ($property->tray_id) {
            // Tray API does not support updating a property with its values in one go.
            // We would need to manage values separately if updates are needed.
            // For now, we will only create new properties.
            throw new \Exception('Updating properties is not yet supported.');
        }

        $response = Http::post($url, $data);

        if ($response->successful()) {
            $responseData = $response->json();
            if (isset($responseData['id'])) {
                $property->update(['tray_id' => $responseData['id']]);
            }
            return $responseData;
        }

        throw new \Exception('Failed to send property to Tray: ' . $response->body());
    }

    public function assignPropertiesToProduct(\App\Models\Product $product)
    {
        if (empty($product->tray_id)) {
            throw new \Exception('Product is not synced to Tray. Please sync the product first.');
        }

        $accessToken = $this->getAccessToken();
        $url = $this->credentials->api_host . '/products/' . $product->tray_id . '/properties';

        $propertiesData = $product->propertyValues->map(function ($propertyValue) {
            if (empty($propertyValue->property->tray_id)) {
                throw new \Exception('Property ' . $propertyValue->property->name . ' is not synced to Tray. Please sync the property first.');
            }
            return [
                'property_id' => $propertyValue->property->tray_id,
                'value' => $propertyValue->name,
            ];
        })->toArray();

        $response = Http::post($url, [
            'access_token' => $accessToken,
            'properties' => $propertiesData,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Failed to assign properties to product on Tray: ' . $response->body());
    }
}
