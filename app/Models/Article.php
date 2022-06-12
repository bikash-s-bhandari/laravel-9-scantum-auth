<?php

namespace App\Models;

use App\Traits\HasAuthor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory,HasAuthor;
    const TABLE='articles';
    protected $table=self::TABLE;
    protected $fillable=[
        'title',
        'slug',
        'body',
        'author_id'

    ];



    //id lai as string return gareko
    public function id():string{
        return (string) $this->id;
    }
    //title lai as string return gareko
    public function title():string{
        return $this->title;
    }
    public function slug():string{
        return $this->slug;
    }
    public function body():string{
        return $this->body;
    }


}
