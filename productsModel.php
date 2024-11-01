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
        B.id as category_id,
        C.id as tipo_id,
        C.nombre as tipo,
        D.id as indice_id,
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

    public function saveProducts($name,$description,$amount,$price,$category,$tipo,$indice,$urlImg){
        $valida = $this->validateProducts($name,$description,$price);
        $resultado=['error','Ya existe un producto con las mismas características'];
        if(count($valida)==0){
            $sql="INSERT INTO products(name,description,price,amount,img,category,tipo,indice) VALUES('$name','$description','$price','$amount','$urlImg','$category','$tipo','$indice')";
            mysqli_query($this->conexion,$sql);
            $resultado=['success','Producto guardado'];
        }
        return $resultado;
    }
    //checar esto 
    public function updateProducts($id,$name,$description,$amount,$price,$category,$tipo,$indice,$urlImg){
        // $existe= $this->getProducts($id);
        // $resultado=['error','No existe el producto con ID '.$id];
        // if(count($existe)>0){
        //     $valida = $this->validateProducts($name,$description,$price);
        //     $resultado=['error','Ya existe un producto las mismas características'];
        //     if(count($valida)==0){
                $sql="UPDATE products SET name='$name',description='$description',amount='$amount',price='$price',category='$category',tipo='$tipo',indice='$indice',img='$urlImg' WHERE id='$id' ";
                mysqli_query($this->conexion,$sql);
                $resultado=['success','Producto actualizado'];
        //     }
        // }
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

    public function getCatalog($catalog){
        
        $products=[];
        $sql = "SELECT * FROM " . $catalog;
        $registos = mysqli_query($this->conexion,$sql);
        
        while($row = mysqli_fetch_assoc($registos)){
            array_push($products,$row);
            // var_dump($row);
        }
        
        return $products;
    }

    //Users
    public function getUser($user, $password){
        $products=[];
        $sql = "SELECT * FROM usuarios where usuario='$user' and password='$password'";
        $registros = mysqli_query($this->conexion,$sql);

        while($row = mysqli_fetch_assoc($registros)){
            array_push($products,$row);
        }
        if(empty($products)){
            return ['error','Usuario o contraseña incorrectos'];
        }
        return $products;
    }
}