<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header('content-type: application/json; charset=utf-8');
require 'productsModel.php';
$productsModel= new productsModel();
switch($_SERVER['REQUEST_METHOD']){
    case 'GET':
        $respuesta = (!isset($_GET['id'])) ? $productsModel->getProducts() : $productsModel->getProducts($_GET['id']);
        echo json_encode($respuesta);
    break;

    case 'POST':
        // Decodificar el cuerpo de la solicitud JSON
        $_POST = json_decode(file_get_contents('php://input', true));
    
        // Validar los campos del producto
        if (!isset($_POST->name) || is_null($_POST->name) || empty(trim($_POST->name)) || strlen($_POST->name) > 80) {
            $respuesta = ['error', 'El nombre del producto no debe estar vacío y no debe de tener más de 80 caracteres'];
        } else if (!isset($_POST->description) || is_null($_POST->description) || empty(trim($_POST->description)) || strlen($_POST->description) > 510) {
            $respuesta = ['error', 'La descripción del producto no debe estar vacía y no debe de tener más de 510 caracteres'];
        } else if (!isset($_POST->price) || is_null($_POST->price) || empty(trim($_POST->price)) || !is_numeric($_POST->price) || strlen($_POST->price) > 20) {
            $respuesta = ['error', 'El precio del producto no debe estar vacío, debe ser de tipo numérico y no tener más de 20 caracteres'];
        } else {
            // var_dump($_FILES['img']);
            // Manejar la carga de la imagenv

            if (isset($_FILES['img']) && $_FILES['img']['error'] == UPLOAD_ERR_OK) {
                $imgTmpPath = $_FILES['img']['tmp_name'];
                $imgName = $_FILES['img']['name'];
                $imgSize = $_FILES['img']['size'];
                $imgType = $_FILES['img']['type'];
                $imgExtension = pathinfo($imgName, PATHINFO_EXTENSION);
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
                if (in_array($imgExtension, $allowedExtensions)) {
                    $uploadDir = 'uploads/';
                    $destPath = $uploadDir . $imgName;
    
                    if (move_uploaded_file($imgTmpPath, $destPath)) {
                        // Guardar el producto en la base de datos
                        $respuesta = $productsModel->saveProducts($_POST->name, $_POST->description, $_POST->price, $_POST->amount, $destPath);
                    } else {
                        $respuesta = ['error', 'Error al mover el archivo de imagen a la ubicación de destino'];
                    }
                } else {
                    $respuesta = ['error', 'Tipo de archivo de imagen no permitido'];
                }
            } else {
                $respuesta = ['error', 'Error al cargar la imagen'];
            }
        }
        echo json_encode($respuesta);
    break;

    case 'PUT':
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
            $respuesta = $productsModel->updateProducts($_PUT->id,$_PUT->name,$_PUT->description,$_PUT->price);
        }
        echo json_encode($respuesta);
    break;

    case 'DELETE';
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