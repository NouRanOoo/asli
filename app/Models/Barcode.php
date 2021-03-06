<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barcode extends Model
{
    use HasFactory;
    protected $fillable = ['code','product_id','scan_before'];
    public function Product()
    {
        return $this->belongsTo(Product::class);
    }

    public function Customer()
    {
     
        return $this->belongsTo(Customer::class, 'customer_barcodes','barcode_id','customer_id');
        
    }
}
