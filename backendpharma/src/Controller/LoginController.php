<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\BdconnexionController as CN;

class LoginController
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
    public function connexion()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $request = Request::createFromGlobals();
        if(strtolower($request->getMethod())=="post"){
           // $body=json_decode($request->getContent());
            $id=$_POST['id'];
            $password=$_POST['password'];
            $req=$this->bdd->prepare("SELECT * FROM users WHERE username=:user AND password=:pass");
            $req->execute(array(":user"=>$id,":pass"=>$password));
            $user=$req->fetch();
            if($user){
                $token=$this->token($user["id_user"]);
                $t=$this->InsertToken($user["id_user"],$token,$user["level"]);
                if($t>=1){
                    $priv=$this->getPrivilege($user["id_user"]);
                    $rep=["status"=>1,"level"=>$user["level"],"token"=>$token,"id"=>$user["id_user"],"idShop"=>$user['idShop'],"privileges"=>$priv];
                    return new Response(json_encode($rep));
                }else{
                    return new Response(json_encode(["status"=>500,"message"=>"problem au niveau du serveur"]));
                }
            }else{
                return new Response(json_encode(["status"=>0]));
            }
        }else{
            return new Response("rasta");
        }
    }
    public function deconnexion(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $request = Request::createFromGlobals();
        if(strtolower($request->getMethod())=="post"){
            $body=json_decode($request->getContent());
            $id=$body->id;
            $del=$this->deleteToken($id);
            if($del){
                return new Response(json_encode(["status"=>1,"data"=>$id]));
            }else{
               return new Response(json_encode(["status"=>0]));
            }
        }else{
            return new Response("hello");
        }
    }
    public function InsertToken($id,$token,$level){
        $this->deleteToken($id);
        $req=$this->bdd->prepare("INSERT INTO token(id_user,token,level) VALUES(:id,:token,:level)");
        $tontou=$req->execute(array(":id"=>$id,":token"=>$token,":level"=>$level));
        return $tontou;
    }
    public function deleteToken($id){
        $req1=$this->bdd->prepare("SELECT * FROM token WHERE id_user=:id");
        $req1->execute(array(":id"=>$id));
        if($req1->fetch()){
            $req2=$this->bdd->prepare("DELETE FROM token WHERE id_user=:id");
            $tont=$req2->execute(array(":id"=>$id));
            return $tont;
        }else{
            return true;
        }
    }
    public function token($id){
        return \sha1(strval(\time()).strval($id).strval(random_int(0, 10000)));
    }
    public function checkConnected($id,$token,$level){
           $req=$this->bdd->prepare("SELECT * FROM token WHERE id_user=:id AND token=:token AND level=:level");
           $req->execute(array(":id"=>$id,":token"=>$token,":level"=>$level));
           if($req->fetch()){
                return 1;
            }else{
                return 0;
            }
       
    }
    public function getPrivilege($id){
        $req=$this->bdd->prepare("SELECT * FROM privileges WHERE id_user=:id");
        $req->execute(array(":id"=>intval($id)));
        if($el=$req->fetch()){
            $el=json_decode($el["privileges"]);
            $ton=array("vente"=>$el->vente,"historique"=>$el->historique,"reappro"=>$el->reappro);
            return $ton;
        }
        return [];
    }
}