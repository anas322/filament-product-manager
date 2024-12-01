<?php

namespace Database\Factories;

use App\Enums\ProductTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'brand_id' => \App\Models\Brand::factory(),
            'name' => $this->faker->name,
            'slug' => $this->faker->slug,
            'sku' => $this->faker->slug,
            'image' => $this->faker->imageUrl(),
            'quantity' => $this->faker->randomNumber(2),
            'price' => $this->faker->randomFloat(2, 1, 1000),
            'description' => $this->faker->text,
            'is_visible' => $this->faker->boolean,
            'is_featured' => $this->faker->boolean,
            'type' => $this->faker->randomElement([ProductTypeEnum::DELIVERABLE->value, ProductTypeEnum::DOWNLOADABLE->value]),
            'published_at' => $this->faker->dateTimeThisYear,

        ];
    }
}
