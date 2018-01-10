<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartialOrder extends Model
{
	protected $table = 'partial_orders';
	protected $fillable = ['total', 'status', 'order_id', 'pay_method', 'associated'];

}
