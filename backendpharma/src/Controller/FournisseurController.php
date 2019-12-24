<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\BdconnexionController as CN;

class FournisseurController
{
    private $bdd;
    public function __construct(){
        $cn=new CN();
        $this->bdd=$cn->getBdd();
    }
    public function addFournisseur(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $token=$_POST['token'];
        $idUser=$_POST['id'];
        $fournisseur=json_decode($_POST['fournisseur']);
        if(!$this->getFournisseurByName($fournisseur->nom,$idUser)){
            $rep=$this->saveFournisseur($fournisseur,$idUser);
            return new Response(json_encode(array("status"=>$rep)));
        }
        return new Response(json_encode(array("status"=>-1)));
        
    }
    public function getListeFournisseur(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $idShop=$_POST['idShop'];
        $liste=[];
        $req=$this->bdd->prepare("SELECT * FROM fournisseur WHERE idShop=:id");
        $req->execute(array(":id"=>intval($idShop)));
        while($f=$req->fetch()){
            $liste[]=array("id"=>$f["id"],"nom"=>$f["nom"],"adresse"=>$f["adresse"]);
        }
        return new Response(json_encode(array("liste"=>$liste)));
        
    }
    public function getFournisseurByName($name,$idUser){
        $req=$this->bdd->prepare("SELECT * FROM fournisseur WHERE nom=:n AND idShop=:id");
        $req->execute(array(":n"=>$name,":id"=>$idUser));
        if($req->fetch()){
            return true;
        }
        return false;
    }
    public function saveFournisseur($f,$idShop){
        $req=$this->bdd->prepare("INSERT INTO fournisseur(nom,adresse,tel,idShop) VALUES(:nom,:adresse,:tel,:idShop)");
        return $req->execute(array(":nom"=>$f->nom,":adresse"=>$f->adresse,":tel"=>$f->tel,":idShop"=>$idShop));
        
    }
   
}