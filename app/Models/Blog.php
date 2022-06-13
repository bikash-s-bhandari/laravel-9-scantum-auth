<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;
    const TABLE='blogs';
    protected $table=self::TABLE;
    protected $fillable=[
        'title',
        'short_description',
        'long_description',
        'user_id',
        'category_id',
        'image'

    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function category(){
        return $this->belongsTo(Category::class);
    }



}
