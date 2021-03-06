<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller {

    //Constructor
    public function __construct() {
        //Cargas middleware, proteger todas las rutas excepto index y show
        $this->middleware('api.auth', ['except' => ['index', 'show', 'getImage','getPostsByCategory','getPostsByUser']]); //Proteger rutas de autenticacion
    }

    public function index() {
        $post = Post::all()->Load('category');

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'posts' => $post
                        ], 200);
    }

    public function show($id) {

        $posts = Post::find($id);

        if (is_object($posts)) {
            $posts->load('category')->load('user');
            
            $data = [
                'code' => 200,
                'status' => 'success',
                'posts' => $posts
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'No existe la entrada'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        //Recojer datos por post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            //Obtener usuario identificado
            $user = $this->getIdentity($request);

            //Validar los datos
            $validate = \Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required',
                        'image' => 'required'
            ]);

            //Guardar el post
            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado el post'
                ];
            } else {
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;

                $post->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post
                ];
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Ingresa los datos correctamente'
            ];
        }

        //Devolver la respuesta
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request) {
        //Recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        
        //Datos para devolver
        $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Datos incorrectos'
            );

        if (!empty($params_array)) {
            //Validar los datos
            $validate = \Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required'
            ]);
            
            if ($validate->fails()) {
                $data['errors']=$validate->errors();
                return response()->json($data,$data['code']);
            }

            //Eliminar campos que no se van a actualizar
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);
            
            //Obtener usuario identificado
            $user = $this->getIdentity($request);

            //Buscar registro
            $post = Post::where('id',$id)->where('user_id',$user->sub)->first();//Conseguir el registro
            
            if (!empty($post) && is_object($post)) {
                
                //Actualizar el registro
                $post->update($params_array);
                
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'post'=> $post,
                    'changes' => $params_array
                );            
            }
            
            /*
            $where = [
                'id'=> $id,
                'user_id'=>$user->sub
            ];
            
            $post = Post::updateOrCreate($where,$params_array);*/
            
        }
        
        //Devolver algo
        return response()->json($data, $data['code']);
    }
    
    public function destroy($id,Request $request){
        
        //Obtener usuario identificado
        $user = $this->getIdentity($request);
        
        //Conseguir el registro
        $post = Post::where('id',$id)->where('user_id',$user->sub)->first();
        
        if(!empty($post)) {
            
            //Borrarlo
            $post->delete();

            $data = [
                'code'=> 200,
                'status'=>'success',
                'post' => $post
            ];
        }else{
            //Mensaje de error si el post no existe
            $data = [
                'code'=> 404,
                'status'=>'error',
                'message' => 'El post no existe'
            ];
        }      
        
        //Devolver algo
        return response()->json($data, $data['code']);
    }
    
    private function getIdentity($request){
        //Conseguir uduario identificado
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);
        
        return $user;
    }

    public function upload(Request $request){
        
        //Recojer los datos de la peticion
        $image = $request->file('file0');
        
        //Validar que el archivo sea una imagen
        $validate = \Validator::make($request->all(),[
            'file0' => 'required|image|mimes:jpg,png,jpeg'
        ]);
        
        //Guardar iamgen en disco images
        if (!$image || $validate->fails()) {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir la imagen'
            ];
        }else{
            
            $image_name = time().$image->getClientOriginalName();//Poner nuevo nombre a la imagen
            \Storage::disk('images')->put($image_name, \File::get($image));//Almacenar imagen en images
            
            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            ];
        }
        
        //Devolver datos
        return response()->json($data,$data['code']);
    }
    
    public function getImage($filename){
        //Comprobar si existe el fichero
        $isset = \Storage::disk('images')->exists($filename);
        //Conseguir imagen
        if ($isset) {
            //Conseguir imagen
            $file = \Storage::disk('images')->get($filename);
            
            //Devolver la imagen
            return new Response($file,200);
        }else{
            $data = [
                'code' => 404,
                'error' => 'error',
                'message' => 'La imagen no existe'
            ];
            
            return response()->json($data,$data['code']);
        }
    }
    
    public function getPostsByCategory($id){
        $posts = Post::where('category_id',$id)->get();
        
        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ],200);
    }
    
    public function getPostsByUser($id){
        $posts = Post::where('user_id',$id)->get();
        
        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ],200);
    }
}
