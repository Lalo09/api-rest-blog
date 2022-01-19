<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller {

    //Constructor
    public function __construct() {
        //Cargas middleware
        $this->middleware('api.auth', ['except' => ['index', 'show']]); //Proteger rutas de autenticacion
    }

    //Todas las categorias
    public function index() {
        $categories = Category::all();

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'categories' => $categories
        ]);
    }

    public function show($id) {
        $categories = Category::find($id);

        if (is_object($categories)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $categories
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La categoria no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        //Recojer los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        //Guardar la categoria
        if (!empty($params_array)) {

            //Validar la informacion
            $validate = \Validator::make($params_array, [
                        'name' => 'required'
            ]);

            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado la categoria'
                ];
            } else {
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'category' => $category
                ];
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No se ha enviado ninguna categoria'
            ];
        }

        //Devolver el resultado
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request) {
        //Recojer datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {

            //Validar los datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required'
            ]);

            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha enviado ninguna categoria'
                ];
            } else {
                //Quitar lo que no quiero actualizar
                unset($params_array['id']);
                unset($params_array['created_at']);

                //Actualizar el registro (una categoria)
                $category = Category::where('id', $id)->update($params_array);

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => $category
                ];
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No se ha enviado ninguna categoria'
            ];
        }

        //Devolver resultado
        return response()->json($data, $data['code']);
    }

}
