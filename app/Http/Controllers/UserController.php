<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller {

    public function pruebas(Request $request) {
        return "accion de pruebas user controller";
    }

    public function register(Request $request) {

        $json = $request->input('json', null);

        //decodificamos de array a json
        $params = json_decode($json);
        //convertimos a array
        $params_array = json_decode($json, true);
        //evitar espacios
        $params_array = array_map("trim", $params_array);
        if (!empty($params) && !empty($params_array)) {
            $validate = \Validator::make($params_array, [
                        'name' => 'required|regex:/^[\pL\s\-]+$/u',
                        'surname' => 'required|regex:/^[\pL\s\-]+$/u',
                        'email' => 'required|email|unique:users',
                        'password' => 'required'
                            /* email unico en la tabla email */
            ]);


            if ($validate->fails()) {
                //validacin fallida
                $data = array(
                    'status' => 'error',
                    'code' => '404',
                    'message' => 'el usuario no se ha creado',
                    'errors' => $validate->errors()
                );
            } else {
                //validacion pasada correctamente
    
                $pwd = hash('sha256', $params->password);
                //crear usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = "ROLE_USER";
                $user->save();


                $data = array(
                    'status' => 'success',
                    'code' => '200',
                    'message' => 'el usuario  se ha creado correctamente',
                    'user' => $user
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => '404',
                'message' => 'Los datos enviados no son correctos'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function login(Request $request) {
        $jwtAuth = new \JwtAuth();

        $json = $request->input('json', null);
        $params = json_decode($json);
        //decodificamos en array
        $params_array = json_decode($json, true);

        $validate = \Validator::make($params_array, [
                    'email' => 'required|email',
                    'password' => 'required'
        ]);

        if ($validate->fails()) {
            $signup = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'el usuario no se ha podido identificar',
                'errors' => $validate->errors()
            );
        } else {
            //codificamos la passs
            $pwd = hash('sha256', $params->password);
            //devolver token
            $signup = $jwtAuth->signup($params->email, $pwd);


            if (!empty($params->getToken)) {
                $signup = $jwtAuth->signup($params->email, $pwd, true);
            }
        }



        return response()->json($signup, 200);
    }

    public function update(Request $request) {
        $token = $request->header('Authorization');

        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);


        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if ($checkToken && !empty($params_array)) {



            $user = $jwtAuth->checkToken($token, true);


            $validate = \Validator::make($params_array, [
                        'email' => 'required|email|unique:users,' . $user->sub,
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha'
            ]);

            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);
             unset($params_array['sub']);
               unset($params_array['iat']);
                unset($params_array['exp']);
            $user_update = User::where('id', $user->sub)->update($params_array);

            $data = array(
                'status' => 'success',
                'code' => 200,
                'user' => $user,
                'changes' => $params_array
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'el usuario no esta identificado'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function upload(Request $request) {
        $image = $request->file('file0');
        
        $validate=\Validator::make($request->all(),[
            'file0'=>'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        if (empty($image) || $validate->fails()) {
               $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'error al subir la imagen'
            );
          
            //para registrar la carpeta user, debemos ir a config/filesystems  y agregar un nuevo item en el arreglo disks
        } else {
           $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'status' => 'success',
                'code' => 200,
                'image' => $image_name
            );
        }

        return response()->json($data, $data['code']);
    }
    public function getImage($filename){
        $iset=\Storage::disk('users')->exists($filename);
   
        
        if($iset){
           $file=\Storage::disk('users')->get($filename);
      
            return new Response($file,200);
        }else{
              $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'error al cargar la imagen'
                      
            );
       return response()->json($data, $data['code']);
        }
    }
    
    public function  detail($id){
        $user=User::find($id);
        if(is_object($user)){
            $data=array(
                'code'=>200,
                'status'=>'sucess',
                'user'=>$user
            );
        }else{
            $data=array(
                'code'=>400,
                'status'=>'error',
                'message'=>'usuario no encontrado'
            );
        }
        return response()->json($data,$data['code']);
    }

}
