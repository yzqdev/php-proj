<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class HomeController extends BaseController
{
   public function  getHomeData(): string
   {
       $users = DB::table('users')->get();
       return $users;
   }
   public function removeAllHome():string{
       return  "remove all";
   }
}
