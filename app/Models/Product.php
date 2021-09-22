<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Product
 *
 * @property int $id
 * @property string $title
 * @property string $sku
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProductVariant[] $productVariant
 * @property-read int|null $product_variant_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProductVariantPrice[] $variantPrices
 * @property-read int|null $variant_prices_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Variant[] $variants
 * @property-read int|null $variants_count
 */
class Product extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function variantPrices()
    {
        return $this->hasMany(ProductVariantPrice::class);
    }
    public function productVariant()
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function variants()
    {
        return $this->belongsToMany(Variant::class, 'product_variants')->distinct();
    }
}
