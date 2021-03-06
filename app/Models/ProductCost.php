<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductCost
 * @package App\Models
 * @property int id
 * @property int increment_id
 * @property string country_id
 * @property double cost
 */
class ProductCost extends Model
{
    public $timestamps = false;
    protected $table = 'product_costs';
    protected $fillable = ['product_id'];
}
