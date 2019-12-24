<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\BdconnexionController as CN;

class VenteController{
    private $bdd;
    public function __construct(){
        $cn=new CN();
        $this->bdd=$cn->getBdd();
    }
    public function getProducts(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $id=$_POST['id'];
        $token=$_POST['token'];
        $level=$_POST['level'];
        $idShop=$_POST['idShop'];
        $prod=[];
        if($this->isConnected($id,$token,$level)){
            $req=$this->bdd->prepare("SELECT * FROM products WHERE idShop=:idShop AND UnitsInStock>0");
            $req->execute(array(':idShop'=>$idShop));
            while($rep=$req->fetch()){
                $prod[]=array("ProductId"=>$rep["ProductId"],"ProductTitle"=>$rep["ProductTitle"],"ProductDescription"=>$rep["ProductDescription"],"NumberUnitsProduct"=>"0","UnitsInStock"=>"0","ReoderLevel"=>"0","Tva"=>"0","SellingPriceOfUnit"=>$rep["SellingPriceOfUnit"],"PurchasePriceOfUnit"=>$rep["PurchasePriceOfUnit"]);
                //array_push($prod,[$rep]);
            }
           // $data=$req->fetchAll();
            return new Response(json_encode(["status"=>1,"data"=>$prod]));
        }else{
            return new Response(json_encode(["status"=>-1]));
        }
    }
    public function isConnected($id,$token,$level){
        $req=$this->bdd->prepare("SELECT * FROM token WHERE token=:token AND id_user=:id AND level=:level");
        $req->execute(array(":token"=>$token,":id"=>$id,":level"=>$level));
        if($req->fetch()){
            return true;
        }else{
            return false;
        }
    }
    public function saveBill(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $billNumber=intval(strval(random_int(0, 9)).strval(random_int(0, 0)).strval(random_int(0, 9)).strval(random_int(0, 9)).strval(random_int(0, 9)).strval(random_int(0, 9)).strval(random_int(0, 9)).strval(random_int(0, 9)).strval(random_int(0, 9)));
        \date_default_timezone_set('UTC');
       $date=\date("Y-m-d H:i:s");
      /* $Date=explode($date," ");
       $da=explode($Date[0],"-");
       $h=explode($Date[1],":");
       $timestamp=mktime(intval($h[0]),intval($h[1]),intval($h[2]),intval($da[1]),intval($da[2]),intval($da[0]));*/
       $timestamp=time();
       $products=$_POST['products'];
       $jsonProducts=json_decode($products);
       $token=$_POST['token'];
       $id=$_POST['id'];
       $level=$_POST['level'];
       $total=$_POST['total'];
       $idShop=$_POST['idShop'];
       $sellType=2;
       $status=2;
       $message="";
       try{
            $req1=$this->bdd->prepare("INSERT INTO bills(BILLNumber,BILLDate,InfoSup,EmployeId,TotalPrice,SellingType,idShop,timestamp) VALUES(:billN,:billD,:inf,:emp,:total,:sellType,:idShop,:timestamp)");
            $req1->execute(array(':billN'=>$billNumber,':billD'=>$date,':inf'=>$products,':emp'=>$id,':total'=>$total,':sellType'=>$sellType,':idShop'=>$idShop,':timestamp'=>$timestamp));
            foreach($jsonProducts as $p){
                $this->saveSelling($billNumber,$p->quantite,$p->ProductId,$p->SellingPriceOfUnit,$id,$idShop,$date);
                $this->updateProduct($p->ProductId,$p->quantite);
            }
            $status=1;
            $message="lep nice";
       }catch(Exception $e){
            $status=-1;
            $message="errors";
       }finally{
           return new Response(json_encode(array("status"=>$status,"message"=>$message)));
       }
    }
    public function saveSelling($billNumber,$nb,$productId,$sellingPrice,$idUser,$idShop,$date){
        $req=$this->bdd->prepare("INSERT INTO selling(BILLNumber,NumberOfUnits,ProductId,SellingPrice,idUser,idShop,date) VALUES(:billN,:NumberOfUnit,:ProductId,:SellingPrice,:idUser,:idShop,:date)");
        $req->execute(array(':billN'=>$billNumber,':NumberOfUnit'=>$nb,':ProductId'=>$productId,':SellingPrice'=>$sellingPrice,':idUser'=>$idUser,':idShop'=>$idShop,':date'=>$date));
    }
    public function updateProduct($idP,$q){
        $req=$this->bdd->prepare("SELECT * FROM products WHERE ProductId=:id");
        $req->execute(array(':id'=>$idP));
        $p=$req->fetch();
        $quantite=$p['UnitsInStock'];
        $newQ=$quantite-$q;
        $req2=$this->bdd->prepare("UPDATE products SET UnitsInStock=:q WHERE ProductId=:id");
        $req2->execute(array(":q"=>$newQ,":id"=>$idP));
        
    }
    public function getProductByDate(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $id=$_POST['id'];
        $idShop=$_POST['idShop'];
        $d=date("Y-m-d");
        $date=$_POST['date']!=""?$_POST['date']:$d;
        $req=$this->bdd->prepare("SELECT * FROM bills WHERE BILLDate LIKE "."'%".strval($date)."%'"." AND EmployeId=:id AND idShop=:idShop");
        $req->execute(array(':id'=>$id,':idShop'=>$idShop));
        $tontou=[];
        while($tuple=$req->fetch()){
            $tontou[]=array("BILLNumber"=>$tuple["BILLNumber"],"BILLDate"=>$tuple["BILLDate"],"InfoSup"=>$tuple["InfoSup"],"TotalPrice"=>$tuple["TotalPrice"]);
        }
        return new Response(json_encode($tontou));
      // return new Response($d);
        
    }
    public function getProductsByInterval(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $id=$_POST['id'];
        $idShop=$_POST['idShop'];
        $dateDeb=explode("-",strval($_POST['dateDeb']));
        $dateFin=explode("-",strval($_POST['dateFin']));
        $level=$_POST['level'];
        $dateDeb=mktime(0,0,0,intval($dateDeb[1]),intval($dateDeb[2]),intval($dateDeb[0]));
        $dateFin=mktime(23,59,59,intval($dateFin[1]),intval($dateFin[2]),intval($dateFin[0]));
        //$req=$this->bdd->prepare("SELECT * FROM bills WHERE BILLDate >=:datedeb AND BILLDate <=:datefin AND idShop=:idShop AND EmployeId=:id");
        switch(intval($level)){
            case 1:{
                $req=$this->bdd->prepare("SELECT * FROM bills WHERE timestamp>=:datedeb AND timestamp<=:datefin AND idShop=:idShop AND EmployeId=:id");
                $req->execute(array(':datedeb'=>$dateDeb,':datefin'=>$dateFin,':idShop'=>$idShop,':id'=>$id));
                 //$req->execute(array(':datedeb'=>$dateDeb,':datefin'=>$dateFin,':idShop'=>$idShop,':id'=>$id));
                $tontou=[];
                while($tuple=$req->fetch()){
                    $tontou[]=array("BILLNumber"=>$tuple["BILLNumber"],"BILLDate"=>$tuple["BILLDate"],"InfoSup"=>$tuple["InfoSup"],"TotalPrice"=>$tuple["TotalPrice"]);
                }
                return new Response(json_encode($tontou));
                break;
               // return new Response("this is rasta");
            }
            case 2:{
                $req=$this->bdd->prepare("SELECT * FROM bills WHERE timestamp>=:datedeb AND timestamp<=:datefin AND idShop=:idShop AND (EmployeId=:id OR EmployeId IN (SELECT id_user FROM users WHERE dependOn=:do) )");
                $req->execute(array(':datedeb'=>$dateDeb,':datefin'=>$dateFin,':idShop'=>$idShop,':id'=>$id,':do'=>$id));
                 //$req->execute(array(':datedeb'=>$dateDeb,':datefin'=>$dateFin,':idShop'=>$idShop,':id'=>$id));
                $tontou=[];
                while($tuple=$req->fetch()){
                    $tontou[]=array("BILLNumber"=>$tuple["BILLNumber"],"BILLDate"=>$tuple["BILLDate"],"InfoSup"=>$tuple["InfoSup"],"TotalPrice"=>$tuple["TotalPrice"]);
                }
                return new Response(json_encode($tontou));
                break;
                
            }
            default:{
                return new Response(json_encode([]));
            }
        }
        
    }
        
}


?>