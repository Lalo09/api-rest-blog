<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller {

    public function pruebas(Request $Request) {
        return "Accion de prueba del controlador de usuario";
    }

    public function register(Request $request) {

        //TEST
        /**
          $name = $request->input('name');
          $surname = $request->input('surname');
          return "Accion de registro de usuario: $name , $surname ";
         * 
         */
        //Recojer los datos del usuario por post
        $json = $request->input('json', null);

        //Decodificar el json
        $params = json_decode($json); //Como objeto
        $params_array = json_decode($json, true); //Como array

        if(!empty($params_array) && !empty($params)) {
            //Limpiar datos
            $params_array = array_map('trim',$params_array);

            //Validar datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users',
                        'password' => 'required'
            ]);

            if ($validate->fails()) {
                //Validacion ha fallado
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha creado',
                    'errors' => $validate->errors()
                );
            } else {
                //Validar correcta
                
                //Cifrar la contraseña
                $pwd = hash('sha256',$params->password);
                
                //Crear el usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';
                
                //Guardar el usuario en db
                $user->save();
                
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario se ha creado satisfactoriamente'
                );
            }
        }
        else{
            $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Los datos enviados no son correctos'
                );
        }
        
        return response()->json($data, $data['code']);
    }

    public function login(Request $request) {
        $jwtAuth = new \JwtAuth();
        
        //Recibir datos por post
        $json = $request->input('json',null);
        $param = json_decode($json);
        $params_array = json_decode($json,true);
        
        //Validar los datos
        $validate = \Validator::make($params_array, [
                        'email' => 'required|email',
                        'password' => 'required'
            ]);

            if ($validate->fails()) {
                //Validacion ha fallado
                $signup = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario o contraseña es incorrecto',
                    'errors' => $validate->errors()
                );
            }else{
                ////Cifrar contraseña
                $pwd = hash('sha256',$param->password);
                
                //Devolver token o datos
                $signup = $jwtAuth->signup($param->email,$pwd);
                if(!empty($param->gettoken)){
                    $signup = $jwtAuth->signup($param->email,$pwd,true);
                }
            }        
        
        return response()->json($signup,200);
    }
    
    public function update(Request $request){
                
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        //Recoger los datos por post
        $json = $request->input('json',null);
        $params_Array = json_decode($json,true);
        $checkToken = $jwtAuth->checkToken($token);
        
        if($checkToken && !empty($params_Array)){
            //Actualizar el usuario
            
            //Sacar usuario identificado
            $user = $jwtAuth->checkToken($token,true);
            
            //Validar datos
            $validate = \Validator::make($params_Array,[
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users,'.$user->sub
            ]);
            
            ////Quitar campos que no se desean actualizar
            unset($params_Array['id']);
            unset($params_Array['role']);
            unset($params_Array['password']);
            unset($params_Array['created_at']);
            unset($params_Array['remember_token']);
            
            //Actualizar el usuario en la base de datos
            $user_update = User::where('id',$user->sub)->update($params_Array);
            
            //Devolver array con el resultado
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user,
                'changes' => $params_Array
            );
            
        }
        else{
            //Mensaje de error
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no esta identificado'
            );
        }
        
        return response()->json($data,$data['code']);
    }
    
    public function upload(Request $request){
        
        //Recojer los datos de la peticion
        $image = $request->file('file0');
        
        //Validar que el archivo sea una imagen
        $validate = \Validator::make($request->all(),[
            'file0' => 'required|image|mimes:jpg,png,jpeg'
        ]);
                
        //Subir y guardar imagen (En laravel la imagenes se almacenan en disco vitual)
        if(!$image || $validate->fails()){
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen'
            );
        }else{
           
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));
            
            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
        }
      
        return response()->json($data,$data['code']);
    }
    
    public function getImage($filename){
        
        $isset = \Storage::disk('users')->exists($filename);
        if ($isset) {
            $file = \Storage::disk('users')->get($filename);
            return new Response($file,200);
        }
        else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe'
            );
            
            return response()->json($data,$data['code']);
        }
    }
    
    public function detail($id){
        $user = User::find($id);
        
        if (is_object($user)) {
            $data = array(
                'code' => 200,
                'status' => 'succes',
                'user' => $user
            );
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El usuario no existe'
            );
        }
        
        return response()->json($data,$data['code']);
    }

}
