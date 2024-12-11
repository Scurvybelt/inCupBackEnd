<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

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

    public function saveProducts($name,$description,$amount,$category,$tipo,$indice,$urlImg){
        $valida = $this->validateProducts($name,$description);
        $resultado=['error','Ya existe un producto con las mismas características'];
        if(count($valida)==0){
            $sql="INSERT INTO products(name,description,amount,img,category,tipo,indice) VALUES('$name','$description','$amount','$urlImg','$category','$tipo','$indice')";
            mysqli_query($this->conexion,$sql);
            $resultado=['success','Producto guardado'];
        }
        return $resultado;
    }
    //checar esto 
    public function updateProducts($id,$name,$description,$amount,$category,$tipo,$indice,$urlImg){
        // $existe= $this->getProducts($id);
        // $resultado=['error','No existe el producto con ID '.$id];
        // if(count($existe)>0){
        //     $valida = $this->validateProducts($name,$description,$price);
        //     $resultado=['error','Ya existe un producto las mismas características'];
        //     if(count($valida)==0){
                $sql="UPDATE products SET name='$name',description='$description',amount='$amount',category='$category',tipo='$tipo',indice='$indice',img='$urlImg' WHERE id='$id' ";
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
    
    public function validateProducts($name,$description){
        $products=[];
        $sql="SELECT * FROM products WHERE name='$name' AND description='$description'";
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

    function isBase64Image($base64) {
        // Verificar si la cadena tiene el formato base64 correcto
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            $data = substr($base64, strpos($base64, ',') + 1);
            $data = base64_decode($data);
    
            // Verificar si la decodificación fue exitosa
            if ($data === false) {
                return false;
            }
    
            // Verificar si el contenido decodificado es una imagen válida
            $image = @imagecreatefromstring($data);
            if ($image === false) {
                return false;
            }
    
            // Liberar la memoria de la imagen
            imagedestroy($image);
    
            return true;
        }
    
        return false;
    }

    function saveBase64Image($base64Image, $uploadDir = 'uploads/') {
        $imgData = str_replace('data:image/png;base64,', '', $base64Image);
        $imgData = str_replace('data:image/jpeg;base64,', '', $imgData);
        $imgData = str_replace(' ', '+', $imgData);
        $imgDecoded = base64_decode($imgData);
    
        // Generar un nombre único para la imagen
        $imgName = uniqid() . '.png';
        $destPath = $uploadDir . $imgName;
    
        if (file_put_contents($destPath, $imgDecoded)) {
            return 'http://api-products.test/' . $destPath;
        } else {
            return false;
        }
    }

    public function sendEmail($asunto,$email,$message,$name,$tel){
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);
        try {
            //Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       ='mail.incup.com.mx';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'ventas@incup.com.mx';                     //SMTP username
            $mail->Password   = 'VentasInCup2025';                         //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         // ENCRYPTION_SMTPS 464 Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('ventas@incup.com.mx', 'Formulario Pagina Web'); //quien lo manda
            $mail->addAddress('ventas@incup.com.mx', 'Formulario Pagina Web');     //Add a recipient quien lo recibe

        
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $asunto;
            $mail->Body = '
            <h3>Hola, soy ' . $name . '</h3>
            <p><strong>Email:</strong> ' . $email . '</p>
            <p><strong>Teléfono:</strong> ' . $tel . '</p>
            <p><strong>Mensaje:</strong></p>
            <p>' . nl2br($message) . '</p>
            ';
        
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            

            if($mail->send()){
                // $_SESSION['stats'] = "thank you contact us - Team Aixa of web Ti";
                // header("Location: {$_SERVER['HTTP_REFERER']}");
                // exit(0);
                return ['success', 'Mensaje enviado'];
            }else{
                return ['error', 'Error al enviar el mensaje'];
                // $_SESSION['stats'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                // header("Location: {$_SERVER['HTTP_REFERER']}");
                // exit(0);
            }
            // echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}