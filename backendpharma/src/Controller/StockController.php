<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\VenteController as Vente;
use App\Controller\BdconnexionController as CN;


class StockController{
    private $bdd;
    private $venteController;
    public function __construct(){
       /* try{
            $this->bdd=new \PDO("mysql:host=localhost;dbname=cloudph1_cloudpharma","cloudph1_sntech","@sntech2019");
            $this->venteController=new Vente();
        }
        catch(Exception $e){
            $this->bdd="";
        }*/
        $cn=new CN();
        $this->bdd=$cn->getBdd();
    }
    
    public function getListProduct(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $id=$_POST['id'];
        $token=$_POST['token'];
        $idShop=$_POST['idShop'];
        return new Response(json_encode(array("data"=>$this->getProduct($idShop))));
    }
    public function getProduct($idShop){
        $req=$this->bdd->prepare("SELECT * FROM products WHERE etat=:etat AND idShop=:idShop");
        $req->execute(array(":etat"=>1,"idShop"=>intval($idShop)));
        $prod=[];
        while($rep=$req->fetch()){
            $prod[]=array("ProductId"=>$rep["ProductId"],"ProductTitle"=>$rep["ProductTitle"],"ProductDescription"=>$rep["ProductDescription"],"NumberUnitsProduct"=>$rep["NumberUnitsProduct"],"UnitsInStock"=>$rep["UnitsInStock"],"ReoderLevel"=>"0","Tva"=>$rep["Tva"],"SellingPriceOfUnit"=>$rep["SellingPriceOfUnit"]);
                //array_push($prod,[$rep]);
        }
        return $prod;
        
    }
    public function updateProduct(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $id=$_POST['id'];
        $token=$_POST['token'];
        $idShop=$_POST['idShop'];
        $idProd=$_POST['ProductId'];
        $param=json_decode($_POST['param']);
        $attribut=json_decode($_POST['attribut']);
        $response=[];
        foreach($attribut as $at){
            if(strcmp($at,"ProductTitle")==0){
                $ton=$this->updateProductTitle($idProd,$param->ProductTitle,$idShop);
                $response[]=array("ProductTitle"=>$ton);
            }
            if(strcmp($at,"ProductDescription")==0){
                $ton=$this->updateDescription($idProd,$param->ProductDescription,$idShop);
                $response[]=array("ProductDescription"=>$ton);
            }
            if(strcmp($at,"UnitsInStock")==0){
                $ton=$this->updateQuantite($idProd,$param->UnitsInStock,$idShop);
                $response[]=array("UnitsInStock"=>$ton);
            }
            if(strcmp($at,"SellingPrice")==0){
                $ton=$this->updateSellingPrice($idProd,$param->SellingPrice,$idShop);
                $response[]=array("SellingPrice"=>$ton);
            }
            if(strcmp($at,"PurchasePriceOfUnit")==0){
                $ton=$this->updatePurchasePrice($idProd,$param->PurchasePriceOfUnit,$idShop);
                $response[]=array("SellingPrice"=>$ton);
            }
            if(strcmp($at,"tva")==0){
                $ton=$this->updateTva($idProd,$param->Tva,$idShop);
                $response[]=array("tva"=>$ton);
            }
            
        }
        return new Response(json_encode(array("id"=>$id,"param"=>$param,"response"=>$response)));
    }
    //cette function permet de mettre a jour le titre d'un produit
    public function updateProductTitle($idProd,$val,$idShop){
        $req=$this->bdd->prepare("UPDATE products SET ProductTitle=:pt WHERE ProductId=:idp AND idShop=:idS");
        return $req->execute(array("pt"=>$val,"idp"=>$idProd,":idS"=>$idShop));
        
    }
    //cette function permet de mettre a jour la description d'un produit
    public function updateDescription($idProd,$val,$idShop){
        $req=$this->bdd->prepare("UPDATE products SET ProductDescription=:pd WHERE ProductId=:idp AND idShop=:idS");
        return $req->execute(array(":pd"=>$val,":idp"=>$idProd,":idS"=>$idShop));
    }
    //function permetant de modifier la quantite
    public function updateQuantite($idProd,$val,$idShop){
        $req=$this->bdd->prepare("UPDATE products SET UnitsInStock=:us WHERE ProductId=:idp AND idShop=:ids");
        return $req->execute(array(":us"=>intval($val),":idp"=>$idProd,":ids"=>$idShop));
    }
    //function allow us to update selling price
    public function updateSellingPrice($idProd,$val,$idShop){
        $req=$this->bdd->prepare("UPDATE products SET SellingPriceOfUnit=:p WHERE ProductId=:idp AND idShop=:ids");
        return $req->execute(array(":p"=>intval($val),":idp"=>$idProd,":ids"=>$idShop));
        
    }
    //function allow us to update PurchasePrice
    public function updatePurchasePrice($idProd,$val,$idShop){
        $req=$this->bdd->prepare("UPDATE products SET PurchasePriceOfUnit=:p WHERE ProductId=:idp AND idShop=:ids");
        return $req->execute(array(":p"=>intval($val),":idp"=>$idProd,":ids"=>$idShop));
        
    }
    //update tva
    public function updateTva($idProd,$val,$idShop){
        $p=$this->getProductById($idProd,$idShop);
       // return true;
        if(intval($p["Tva"])!=intval($val)){
            $req=$this->bdd->prepare("UPDATE products SET Tva=:tva WHERE ProductId=:idp AND idShop=:ids");
            $rep=$req->execute(array("tva"=>intval($val),":idp"=>$idProd,":ids"=>$idShop));
            if($rep){
                $prixvente=$p["SellingPriceOfUnit"];
                $prixtva=0;
                if($p["Tva"]==0){
                    $prixtva=$prixvente+$prixvente*0.18;
                }
                if($p["Tva"]==1){
                    $prixtva=$prixvente-$prixvente*0.18;
                }
                $t=$this->updateSellingPrice($idProd,$prixtva,$idShop);
                return $t;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    public function getProductById($idProd,$idShop){
        $req=$this->bdd->prepare("SELECT * FROM products WHERE ProductId=:idp AND idShop=:ids");
        $req->execute(array(":idp"=>$idProd,":ids"=>$idShop));
        $p=$req->fetch();
        return $p;
    }
    public function addProduct(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $id=$_POST['id'];
        $token=$_POST['token'];
        $idShop=$_POST['idShop'];
        $product=json_decode($_POST['product']);
        if(!$this->getProductByName($product->nom,$idShop)){
            $req=$this->bdd->prepare("INSERT INTO products(ProductTitle,ProductDescription,NumberUnitsProduct,UnitsInStock,ReoderLevel,Tva,SellingPriceOfUnit,PurchasePriceOfUnit,CategorieId,peremption,rayon,idShop,idUser) VALUES(:ProductTitle,:ProductDescription,:NumberUnitsProduct,:UnitsInStock,:ReoderLevel,:Tva,:SellingPriceOfUnit,:PurchasePriceOfUnit,:CategorieId,:peremption,:rayon,:idShop,:idUser)");
            $t=$req->execute(array(":ProductTitle"=>$product->nom,
                                  ":ProductDescription"=>$product->description,
                                  ":NumberUnitsProduct"=>$product->quantite,
                                  ":UnitsInStock"=>$product->quantite,
                                  ":ReoderLevel"=>0,
                                  ":Tva"=>$product->sellingPrice,
                                  ":SellingPriceOfUnit"=>$product->sellingPrice,
                                  ":PurchasePriceOfUnit"=>$product->purchasePrice,
                                  ":CategorieId"=>0,
                                  ":peremption"=>$product->peremption,
                                  ":rayon"=>$product->rayon,
                                  ":idShop"=>$idShop,
                                  ":idUser"=>$id));
            if($t){
                return new Response(json_encode(array("status"=>1,"t"=>$t)));
            }else{
                return new Response(json_encode(array("status"=>0,"t"=>$t)));
            }
        }else{
            return new Response(json_encode(array("status"=>-1)));
        }
    }
    public function getProductByName($prod,$idShop){
        $req=$this->bdd->prepare("SELECT * FROM products WHERE ProductTitle=:p AND idShop=:idShop");
        $req->execute(array(":p"=>$prod,":idShop"=>$idShop));
        $p=$req->fetch();
        if($p){
            return true;
        }else{
            return false;
        }
    }
    
    public function saveNewFacture($numfacture,$numfournisseur,$idUser,$products,$datefournisseur,$idShop){
        if($this->checkFacture($numfacture,$numfournisseur)==false){
            $req=$this->bdd->prepare("INSERT INTO reappro(numFacture,numFournisseur,idUser,produits,dateFournisseur,idShop) VALUES(:numFacture,:numFournisseur,:idUser,:produits,:dateFournisseur,:idShop)");
            $t=$req->execute(array(
                    ":numFacture"=>$numfacture,
                    ":numFournisseur"=>$numfournisseur,
                    ":idUser"=>$idUser,
                    ":produits"=>$products,
                    ":dateFournisseur"=>$datefournisseur,
                    ":idShop"=>$idShop
                    ));
            if($t){
                return 1;
            }else{
                return 0;
            }
        }else{
            return -1;
        }
        
    }
    public function reappro(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        $id=$_POST['id'];
        $token=$_POST['token'];
        $idShop=$_POST['idShop'];
        $level=$_POST['level'];
        $numFacture=$_POST['numFacture'];
        $numFournisseur=$_POST['fournisseur'];
        $products=json_decode($_POST['products']);
        if($this->venteController->isConnected($id,$token,$level)){
            \date_default_timezone_set('UTC');
            $date=\date("Y-m-d H:i:s");
            $ton=$this->saveNewFacture($numFacture,$numFournisseur,$id,$_POST['products'],$date,$idShop);
            $repProd=[];
            if($ton==1){
                foreach($products as $el){
                    $t=$this->addQuantite(intval($el->ProductId),$idShop,$el->quantite);
                    $repProd[]=array($el->ProductTitle=>$t);
                    $this->addNumberUnitsInStock($el->ProductId,$idShop,$el->quantite);
                    $this->incremanteRoderLevel(intval($el->ProductId),$idShop);
                }
            }
            return new Response(json_encode(array("status"=>1,"data"=>$products,"ton"=>$ton,"repProd"=>$repProd)));
        }else{
            return new Response(json_encode(array("status"=>-1)));
        }
        
    }
    public function checkFacture($numfacture,$numfournisseur){
        $req=$this->bdd->prepare("SELECT * FROM reappro WHERE numFacture=:numf AND numFournisseur=:numfour");
        $req->execute(array(":numf"=>$numfacture,":numfour"=>intval($numfournisseur)));
        $f=$req->fetch();
        $tontou=false;
        if($f){
            $tontou=true;
        }
        return $tontou;
    }
    public function addQuantite($idprod,$idShop,$quantite){
        $p=$this->getProductById($idprod,$idShop);
        $rep=0;
        if($p){
            $newQuantite=intval($p['UnitsInStock'])+intval($quantite);
            $rep=$this->updateQuantite($idprod,$newQuantite,$idShop);
        }
        return $rep;
    }
    public function addNumberUnitsInStock($idprod,$idShop,$quantite){
        $req=$this->bdd->prepare("SELECT * FROM products WHERE ProductId=:pid");
        $req->execute(array(":pid"=>$idprod));
        $p=$req->fetch();
        if($p){
            $nb=$p["NumberUnitsProduct"]+$quantite;
            $req2=$this->bdd->prepare("UPDATE products SET NumberUnitsProduct=:nb WHERE ProductId=:pid AND idShop=:idShop");
            $req2->execute(array(":nb"=>$nb,":pid"=>intval($idprod),":idShop"=>$idShop));
        }
    }
    public function incremanteRoderLevel($idprod,$idShop){
        $req=$this->bdd->prepare("SELECT * FROM products WHERE ProductId=:pid");
        $req->execute(array(":pid"=>$idprod));
        $p=$req->fetch();
        if($p){
            $nb=intval($p["ReoderLevel"])+1;
            $req2=$this->bdd->prepare("UPDATE products SET ReoderLevel=:nb WHERE ProductId=:pid AND idShop=:idShop");
            $req2->execute(array(":nb"=>$nb,":pid"=>intval($idprod),":idShop"=>$idShop));
        }
    }
}