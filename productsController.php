<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header('content-type: application/json; charset=utf-8');
require 'productsModel.php';
$productsModel= new productsModel();

$requestUri = $_SERVER['REQUEST_URI'];
switch($_SERVER['REQUEST_METHOD']){
    
    case 'GET':
        if(isset($_GET['catalogo'])){
            $respuesta = $productsModel->getCatalog($_GET['catalogo']);
        }else{
            $respuesta = (!isset($_GET['id'])) ? $productsModel->getProducts() : $productsModel->getProducts($_GET['id']);  
        }
        
        // var_dump($_GET['catalogo']);
        
        echo json_encode($respuesta);
    break;

    case 'POST':


        // Decodificar el cuerpo de la solicitud JSON
        $_POST = json_decode(file_get_contents('php://input', true));

        if(strpos($requestUri, 'login') !== false){
            // var_dump('Entro Usre');
            // var_dump($_POST->usuario);
            // var_dump($_POST->password);

            $respuesta = $productsModel->getUser($_POST->usuario, $_POST->password);
        }else{
            if (!isset($_POST->name) || is_null($_POST->name) || empty(trim($_POST->name)) || strlen($_POST->name) > 80) {
                $respuesta = ['error', 'El nombre del producto no debe estar vacío y no debe de tener más de 80 caracteres'];
            } else if (!isset($_POST->description) || is_null($_POST->description) || empty(trim($_POST->description)) || strlen($_POST->description) > 510) {
                $respuesta = ['error', 'La descripción del producto no debe estar vacía y no debe de tener más de 510 caracteres'];
            } else if (!isset($_POST->price) || is_null($_POST->price) || empty(trim($_POST->price)) || !is_numeric($_POST->price) || strlen($_POST->price) > 20) {
                $respuesta = ['error', 'El precio del producto no debe estar vacío, debe ser de tipo numérico y no tener más de 20 caracteres'];
            } else {
                // Manejar la imagen base64
                if (isset($_POST->img)) {
                    $imgData = $_POST->img;
                    $imgData = str_replace('data:image/png;base64,', '', $imgData);
                    $imgData = str_replace('data:image/jpeg;base64,', '', $imgData);
                    $imgData = str_replace(' ', '+', $imgData);
                    $imgDecoded = base64_decode($imgData);
    
                    // Generar un nombre único para la imagen
                    $imgName = uniqid() . '.png';
                    $uploadDir = 'uploads/';
                    $destPath = $uploadDir . $imgName;
    
                    // var_dump ($destPath);
                    
                    if (file_put_contents($destPath, $imgDecoded)) {
                        // Guardar el producto en la base de datos
                        $urlLocal = 'http://api-products.test/'.$destPath;
                        $respuesta = $productsModel->saveProducts($_POST->name, $_POST->description, $_POST->amount, $_POST->price,$_POST->category,$_POST->tipo,$_POST->indice,$urlLocal);
                    } else {
                        $respuesta = ['error', 'Error al guardar la imagen decodificada'];
                    }
                } else {
                    // Guardar el producto sin imagen
                    $respuesta = ['error', 'En la imagen'];
                    // $respuesta = $productsModel->saveProducts($data['name'], $data['description'], $data['price'], $data['amount'], null);
                }
            }
        }
        // Validar los campos del producto
        echo json_encode($respuesta);
    break;

    case 'PUT':
        // var_dump($requestUri);
        $_PUT= json_decode(file_get_contents('php://input',true));
        if(!isset($_PUT->id) || is_null($_PUT->id) || empty(trim($_PUT->id))){
            $respuesta= ['error','El ID del producto no debe estar vacío'];
        }
        else if(!isset($_PUT->name) || is_null($_PUT->name) || empty(trim($_PUT->name)) || strlen($_PUT->name) > 80){
            $respuesta= ['error','El nombre del producto no debe estar vacío y no debe de tener más de 80 caracteres'];
        }
        else if(!isset($_PUT->description) || is_null($_PUT->description) || empty(trim($_PUT->description)) || strlen($_PUT->description) > 150){
            $respuesta= ['error','La descripción del producto no debe estar vacía y no debe de tener más de 150 caracteres'];
        }
        else if(!isset($_PUT->price) || is_null($_PUT->price) || empty(trim($_PUT->price)) || !is_numeric($_PUT->price) || strlen($_PUT->price) > 20){
            $respuesta= ['error','El precio del producto no debe estar vacío , debe ser de tipo numérico y no tener más de 20 caracteres'];
        }
        else{
            
            $respuesta = $productsModel->updateProducts($_PUT->id,$_PUT->name, $_PUT->description, $_PUT->amount, $_PUT->price,$_PUT->category,$_PUT->tipo,$_PUT->indice,$_PUT->img);

        }
        echo json_encode($respuesta);
    break;

    case 'DELETE':
        $_DELETE= json_decode(file_get_contents('php://input',true));
        if(!isset($_DELETE->id) || is_null($_DELETE->id) || empty(trim($_DELETE->id))){
            $respuesta= ['error','El ID del producto no debe estar vacío'];
        }
        else{
            $respuesta = $productsModel->deleteProducts($_DELETE->id);
        }
        echo json_encode($respuesta);
    break;
}