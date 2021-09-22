<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ProductVariantPrice
 *
 * @property int $id
 * @property int|null $product_variant_one
 * @property int|null $product_variant_two
 * @property int|null $product_variant_three
 * @property float $price
 * @property int $stock
 * @property int $product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariantPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariantPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariantPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariantPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariantPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariantPrice wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariantPrice whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariantPrice whereProductVariantOne($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariantPrice whereProductVariantThree($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariantPrice whereProductVariantTwo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariantPrice whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariantPrice whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read mixed $combination_number
 * @property-read mixed $combination_variant
 * @property-read \App\Models\ProductVariant|null $variantOne
 * @property-read \App\Models\ProductVariant|null $variantThree
 * @property-read \App\Models\ProductVariant|null $variantTwo
 */
class ProductVariantPrice extends Model
{

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['combination_variant', 'combination_number'];

    public function variantOne()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_one')->withDefault();
    }
    public function variantTwo()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_two')->withDefault();
    }
    public function variantThree()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_three')->withDefault();
    }

    public function getCombinationVariantAttribute()
    {
        $combination = [];
        if (isset($this->variantOne->variant) && $this->variantOne->variant) {
            array_push($combination, $this->variantOne->variant);
        }
        if (isset($this->variantTwo->variant) && $this->variantTwo->variant) {
            array_push($combination, $this->variantTwo->variant);
        }
        if (isset($this->variantThree->variant) && $this->variantThree->variant) {
            array_push($combination, $this->variantThree->variant);
        }
        return implode('/', $combination);
    }

    public function getCombinationNumberAttribute()
    {
        $combination = [];
        if (isset($this->variantOne->variant_id) && $this->variantOne->variant_id) {
            array_push($combination, $this->variantOne->variant_id);
        }
        if (isset($this->variantTwo->variant_id) && $this->variantTwo->variant_id) {
            array_push($combination, $this->variantTwo->variant_id);
        }
        if (isset($this->variantThree->variant_id) && $this->variantThree->variant_id) {
            array_push($combination, $this->variantThree->variant_id);
        }
        return implode('/', $combination);
    }
}
