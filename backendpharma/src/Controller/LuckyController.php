<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class LuckyController
{
    public function number()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $request = Request::createFromGlobals();
        if($request->getMethod()=="POST"){
            $number = random_int(0, 100);
            //$body=$request->getContent();
            $rep=["status"=>1,"level"=>1,"token"=>"cdjddydy"];
            return new Response(json_encode($rep));
        }else{
            return new Response("rasta");
        }
            
        
    }
}