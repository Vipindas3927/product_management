<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'code','quantity', 'details', 'added_by', 'status'];

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function addBy()
    {
        return $this->hasOne(User::class, 'id', 'added_by');
    }
}
