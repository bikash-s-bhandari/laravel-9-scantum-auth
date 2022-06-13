<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;

class Controller extends BaseController
{
    /**
 * @OA\Info(
 *   version="1.0.0",
 *   title="My API",
 *   @OA\License(name="MIT"),
 *   @OA\Attachable()
 * )
 */
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
