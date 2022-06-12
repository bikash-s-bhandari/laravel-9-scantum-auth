<?php
namespace App\Traits;
/**
 *
 */
trait ModelHelper
{
    public function matches(self $model):bool{
        return $this->id===$model->id;


    }

}

