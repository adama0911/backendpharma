<?php 
namespace App\Controller;

class BdconnexionController
{
    private $bdd;
    public function __construct(){
        try{
            $this->bdd=new \PDO("mysql:host=localhost;dbname=cloudph1_cloudpharma","cloudph1_sntech","M@sntech2019");
        }catch(Exception $e){
            $this->bdd="";
        }
    }
    public function getBdd(){
        return $this->bdd;
    }
}
?>