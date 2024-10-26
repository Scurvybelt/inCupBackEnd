<?php
class productsModel{
    public $conexion;
    public function __construct(){
        $this->conexion = new mysqli('127.0.0.1','root','root','inCup');
        mysqli_set_charset($this->conexion,'utf8');
    }

    public function getProducts($id=null){
        $where = ($id == null) ? "" : " WHERE A.id='$id'";
        $products=[];
        // $sql="SELECT * FROM products ".$where;
        $sql = "SELECT 
        A.id,
        A.name,
        A.description,
        FORMAT(A.price,2) as price,
        A.amount,
        A.img,
        B.nombre as category,
        C.nombre as tipo,
        D.nombre as indice
        From products A
        JOIN category B ON B.id = A.category
        JOIN tipo C ON C.id = A.tipo
        JOIN indice D ON D.id = A.indice". $where;

        $registos = mysqli_query($this->conexion,$sql);
        while($row = mysqli_fetch_assoc($registos)){
            array_push($products,$row);
        }
        return $products;
    }

    public function saveProducts($name,$description,$price,$amount,$img){
        $valida = $this->validateProducts($name,$description,$price);
        $resultado=['error','Ya existe un producto las mismas características'];
        if(count($valida)==0){
            $sql="INSERT INTO products(name,description,price,amount,img) VALUES('$name','$description','$price','$amount','$img')";
            mysqli_query($this->conexion,$sql);
            $resultado=['success','Producto guardado'];
        }
        return $resultado;
    }

    public function updateProducts($id,$name,$description,$price){
        $existe= $this->getProducts($id);
        $resultado=['error','No existe el producto con ID '.$id];
        if(count($existe)>0){
            $valida = $this->validateProducts($name,$description,$price);
            $resultado=['error','Ya existe un producto las mismas características'];
            if(count($valida)==0){
                $sql="UPDATE products SET name='$name',description='$description',price='$price' WHERE id='$id' ";
                mysqli_query($this->conexion,$sql);
                $resultado=['success','Producto actualizado'];
            }
        }
        return $resultado;
    }
    
    public function deleteProducts($id){
        $valida = $this->getProducts($id);
        $resultado=['error','No existe el producto con ID '.$id];
        if(count($valida)>0){
            $sql="DELETE FROM products WHERE id='$id' ";
            mysqli_query($this->conexion,$sql);
            $resultado=['success','Producto eliminado'];
        }
        return $resultado;
    }
    
    public function validateProducts($name,$description,$price){
        $products=[];
        $sql="SELECT * FROM products WHERE name='$name' AND description='$description' AND price='$price' ";
        $registos = mysqli_query($this->conexion,$sql);
        while($row = mysqli_fetch_assoc($registos)){
            array_push($products,$row);
        }
        return $products;
    }
}