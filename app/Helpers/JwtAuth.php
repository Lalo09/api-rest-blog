<?php
namespace App\Helpers;


use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth{
    
    public $key;
    
    public function __construct() {
        $this->key = 'Esto_es_una_clave_muy_secreta-96***';
    }
    
    public function signup($email,$password, $getToken = null){
    //Buscar si existe el usuario con las credenciales
    $user = User::where([
        'email' => $email,
        'password' => $password
    ])->first();
        
    //Comprobar si son correctas(Si devuelve un objeto
    $signup = false;
    if(is_object($user)){
        $signup = true;
    }
    
    //Generar el token con los datos del usuario identificado
    if($signup){
        $token = array(
            'sub' => $user->id,
            'email'=>$user->email,
            'name'=>$user->name,
            'surname'=>$user->surname,
            'description'=>$user->description,
            'image' => $user->image,
            'iat'=>time(),
            'exp'=>time()+7*24*60*60
        );
        
        $jwt = JWT::encode($token, $this->key,'HS256');
        $decode = JWT::decode($jwt, $this->key,['HS256']);
        
        //Devolver los datos decodificados o el token en funcion de un parametro
        if(is_null($getToken)){
            $data = $jwt;
        }
        else{
            $data = $decode;
        }
    }
    else{
        $data = array(
            'status' => 'error',
            'message' => 'Login incorrecto.'
        );
    }
       
    
    return $data;       
    }
    
    public function checkToken($jwt,$getIdentity = false){
        $auth = false;
        try {
            $jwt = str_replace('"','',$jwt);
            $decode = JWT::decode($jwt, $this->key,['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        }catch(\DomainException $e){
            $auth = false;
        }
        
        if(!empty($decode) && is_object($decode) && isset($decode->sub)){
            $auth = true;
        }else{
            $auth = false;
        }
        
        if($getIdentity){
            return $decode;
        }
        
        return $auth;
    }
    
}