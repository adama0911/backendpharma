<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\BdconnexionController as CN;

class RayonController
{
    private $bdd;
    public function __construct(){
        $cn=new CN();
        $this->bdd=$cn->getBdd();
    }
    public function addNewRayon(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $idShop=$_POST['idShop'];
        $rayon=$_POST['rayon'];
        $description=$_POST['description'];
        if($this->checkRayon($idShop,$rayon)==0){
            $this->saveRayon($idShop,$rayon,$description);
            return new Response(json_encode(array("status"=>1)));
        }else{
            return new Response(json_encode(array("status"=>-1,"message"=>"ce rayon existe deja")));
        }
    }
    public function checkRayon($idShop,$rayon){
        $req=$this->bdd->prepare("SELECT * FROM rayon WHERE nom=:nom AND idShop=:idShop");
        $req->execute(array("nom"=>$rayon,"idShop"=>$idShop));
        if($req->fetch()){
            return 1;
        }
        return 0;
    }
    public function saveRayon($idShop,$rayon,$description){
        $req=$this->bdd->prepare("INSERT INTO rayon(nom,description,etat,idShop) VALUES(:nom,:description,:etat,:idShop)");
        $req->execute(array(":nom"=>$rayon,":description"=>$description,":etat"=>1,"idShop"=>$idShop));
        
    }
    public function getRayonByIdShop(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $idShop=$_POST['idShop'];
        $liste=[];
        $req=$this->bdd->prepare("SELECT * FROM rayon WHERE idShop=:idShop");
        $req->execute(array("idShop"=>$idShop));
        while($l=$req->fetch()){
            $liste[]=array("id"=>$l['id'],"nom"=>$l['nom'],"description"=>$l['description']);
        }
        return new Response(json_encode(array("liste"=>$liste)));
    }
}