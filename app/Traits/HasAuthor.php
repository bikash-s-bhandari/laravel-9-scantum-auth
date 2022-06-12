<?php
namespace App\Traits;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasAuthor
{
    public function author():User
    {
        return $this->authorRelation;
    }

    public function authorRelation():BelongsTo{

        return $this->belongsTo(User::class,'author_id');


    }

    //article current author ko ho ki nai vanera check garxa
    public function isAuthoredBy(User $user):bool{

        return $this->author()->matches($user);


    }
    //article create huda feri pass vako author linked hunxa, assigns the author
    public function authoredBy(User $author){

        return $this->authorRelation()->associate($author);


    }

}


