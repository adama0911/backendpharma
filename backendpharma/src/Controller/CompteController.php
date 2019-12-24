<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\BdconnexionController as CN;
use App\Controller\LoginController as Lc;
use App\Controller\ProfilController as Profile;

class CompteController
{
    private $bdd;
    private $lc;
    private $profile;
    public function __construct(){
        $cn=new CN();
        $this->bdd=$cn->getBdd();
        $this->lc=new Lc();
        $this->profile=new Profile();
    }
    public function getCompte(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $id=$_POST['id'];
        $req=$this->bdd->prepare("SELECT * FROM users WHERE dependOn=:do");
        $req->execute(array(":do"=>$id));
        $rep=[];
        while($p=$req->fetch()){
            $rep[]=array("idCompte"=>$p["id_user"],"prenom"=>$p["prenom"],"nom"=>$p["nom"],"username"=>$p["username"],"etat"=>$p["etat"]);
        }
        return new Response(json_encode($rep));
    }
    public function addCompte(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $id=$_POST['id'];
        $idShop=$_POST['idShop'];
        $compte=json_decode($_POST['compte']);
        if(!$this->checkCompte($compte->username)){
            $tontou=$this->saveNewCompte($id,$idShop,$compte);
            return new Response(json_encode(array("status"=>1,"tontou"=>$tontou)));
        }else{
            return new Response(json_encode(array("status"=>-1)));
        }
    }
    public function saveNewCompte($id,$idShop,$compte){
        $req=$this->bdd->prepare("INSERT INTO users(prenom,nom,username,password,level,etat,dependOn,idShop) VALUES(:prenom,:nom,:username,:password,:level,:etat,:dependOn,:idShop)");
        $rep=$req->execute(array(":prenom"=>$compte->prenom,":nom"=>$compte->nom,":username"=>$compte->username,":password"=>$compte->password,":level"=>1,":etat"=>1,":dependOn"=>intval($id),":idShop"=>intval($idShop)));
        return $rep;
    }
    public function checkCompte($username){
        $req=$this->bdd->prepare("SELECT * FROM users WHERE username=:user");
        $req->execute(array(":user"=>$username));
        return $req->fetch();
    }
    public function getPrivilege(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $id=$_POST['id'];
        $req=$this->bdd->prepare("SELECT * FROM privileges WHERE id_user=:id");
        $req->execute(array(":id"=>intval($id)));
        $p=$req->fetch();
        if($p){
            $pr=json_decode($p["privileges"]);
            $priv=array("vente"=>$pr->vente,"historique"=>$pr->historique,"reappro"=>$pr->reappro);
            return new Response(json_encode(array("privilege"=>$priv)));
        }else{
            $priv=array("vente"=>0,"historique"=>0,"reappro"=>0);
            return new Response(json_encode(array("privilege"=>$priv)));
        }
        
    }
    public function activerUser(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $iduser=$_POST['iduser'];
        $idCompte=$_POST['idCompte'];
        $idShop=$_POST['idShop'];
        $token=$_POST['token'];
        $level=$_POST['level'];
        $action=intval($_POST['action']);
        $isConnected=$this->lc->checkConnected($iduser,$token,$level);
       // return new Response(json_encode(array("status"=>$isConnected)));
        if($isConnected==1){
             $req=$this->bdd->prepare("UPDATE users SET etat=:etat WHERE id_user=:id");
            $tontou=$req->execute(array("etat"=>$action,":id"=>$idCompte));
            if($tontou){
                return new Response(json_encode(array("status"=>1)));
            }else{
                return new Response(json_encode(array("status"=>0)));
            }
            
        }else{
            //valeur de retour -1 utilisateur non connecte
            return new Response(json_encode(array("status"=>-1)));
        }
    }
    public function updatePassword(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
       /* $iduser=$_POST['iduser'];
        
        $idShop=$_POST['idShop'];
        $token=$_POST['token'];
        $level=$_POST['level'];*/
        $idCompte=$_POST['idCompte'];
        $password=$_POST['password'];
        $tontou=$this->profile->updatePassword($idCompte,$password);
        $t=0;
        if($tontou){
            $t=1;
        }
        return new Response(json_encode(array("status"=>$t)));
        
    }
        
}