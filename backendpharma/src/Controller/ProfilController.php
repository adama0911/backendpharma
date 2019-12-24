<?php
namespace App\Controller;

use Symfony\Conponent\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\BdconnexionController as CN;
class ProfilController
{
    private $bdd;
    public function __construct(){
       /* try{
            $this->bdd=new \PDO("mysql:host=localhost;dbname=cloudph1_cloudpharma","cloudph1_sntech","@sntech2019");
        }
        catch(Exception $e){
            $this->bdd="";
        }*/
        $cn=new CN();
        $this->bdd=$cn->getBdd();
    }
    public function getProfil(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $id=$_POST["id"];
        $token=$_POST["token"];
        $idShop=$_POST["idShop"];
        $level=$_POST["level"];
        $req=$this->bdd->prepare("SELECT * FROM users WHERE id_user=:id");
        $req->execute(array(":id"=>$id));
        $u=$req->fetch();
        if($u){
            return new Response(json_encode(array("status"=>1,"prenom"=>$u["prenom"],"nom"=>$u["nom"],"username"=>$u["username"],"password"=>$u["password"])));
        }else{
            return new Response(json_encode(array("status"=>0)));
        }
    }
    public function updateProfil(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $id=$_POST["id"];
        $keys=json_decode($_POST["keys"]);
        $valeur=json_decode($_POST["valeur"]);
        $reponse=[];
        $cles=[];
        foreach($keys as $k){
            if(strcmp($k,"prenom")==0){
                $r=$this->updatePrenom($id,$valeur->prenom);
                $reponse[]=["prenom",$r];
                array_push($cles,"prenom");
            }
            if(strcmp($k,"nom")==0){
                $r=$this->updateNom($id,$valeur->nom);
                $reponse[]=["nom",$r];
                array_push($cles,"nom");
            }
             if(strcmp($k,"username")==0){
                $r=$this->updateUsername($id,$valeur->username);
                $reponse[]=["username",$r];
                array_push($cles,"username");
            }
            if(strcmp($k,"password")==0){
                $r=$this->updatePassword($id,$valeur->password);
                $reponse[]=["password",$r];
                array_push($cles,"password");
            }
        }
       // $rep=$this->updatePassword($id,"rasta2");
        return new Response(json_encode(array("reponse"=>$reponse,"keys"=>$cles)));
    }
        
    public function updatePassword($id,$value){
        $req=$this->bdd->prepare("UPDATE users SET password=:pass WHERE id_user=:id");
        $t=$req->execute(array(":pass"=>$value,":id"=>$id));
        return $t;
    }
    public function updatePrenom($id,$value){
        $req=$this->bdd->prepare("UPDATE users SET prenom=:prenom WHERE id_user=:id");
        $t=$req->execute(array(":prenom"=>$value,":id"=>$id));
        return $t;
    }
    public function updateNom($id,$value){
        $req=$this->bdd->prepare("UPDATE users SET nom=:nom WHERE id_user=:id");
        $t=$req->execute(array(":nom"=>$value,":id"=>$id));
        return $t;
    }
     public function updateUsername($id,$value){
         if($this->checkUsername($value)==false){
            $req=$this->bdd->prepare("UPDATE users SET username=:user WHERE id_user=:id");
            $t=$req->execute(array(":user"=>$value,":id"=>$id));
            return $t;
         }else{
             return -1;
         }
    }
    public function checkUsername($username){
        $req=$this->bdd->prepare("SELECT * FROM users WHERE username=:u");
        $req->execute(array(":u"=>$username));
        $u=$req->fetch();
        if($u){
            return true;
        }else{
            return false;
        }
        
    }
}