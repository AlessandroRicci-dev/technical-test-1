<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Product
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property float $price
 * @property int $stock_quantity
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property-read OrderItem[] $orderItems
 *
 * @method static Product|null find(int $id)
 * @method static Product create(array $attributes)
 * @method static Product where(string $column, string $operator = '=', mixed $value)
 * @method static Product lockForUpdate()
 * @method void decrement(string $column, int $amount = 1)
 * @method void increment(string $column, int $amount = 1)
 */
class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'price',
        'stock_quantity',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [];
}
