<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\JwtAuth;

class PostController extends Controller {

    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show','postsByCategory','postsByUser','getImage']]);
    }

    public function index() {
        $post = Post::all()->load('Category');

        return response()->json([
                    'status' => 'success',
                    'code' => '200',
                    'post' => $post
                        ], 200);
    }

    public function show($id) {

        $post = Post::find($id);
        if ($post != null) {
            $post->load('category')->load('user');
            return $data = [
                'status' => 'success',
                'code' => 200,
                'post' => $post
            ];
        } else {
            return $data = [
                'status' => 'errors',
                'code' => 400,
                'message' => 'Post no encontrado'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        //la autenticacion esta activada por el middleware
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        $user = $this->getIdentity($request);

        if (!empty($params_array)) {
            $validate = \Validator::make($params_array, [''
                        .
                        'title' => 'required',
                        'category_id' => 'required',
                        'content' => 'required'
                        
            ]);

            if ($validate->fails()) {
                $data = [
                    'status' => 'errors',
                    'code' => 400,
                    'message' => 'Faltan datos'
                ];
            } else {
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params_array['category_id'];
                $post->title = $params_array['title'];
                $post->content = $params_array['content'];
                $post->image = $params_array['image'];
                $post->save();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'post' => $post
                ];
            }
        } else {
            $data = [
                'status' => 'errors',
                'code' => 400,
                'message' => 'Categoria vacia'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request) {
        $json=null;
        $params_array=null;
        $post=null;
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        $data = [
            "status" => "errors",
            "code" => 400,
            "message" => "Datos incompletos"
        ];
        if (empty($params_array)) {
            return response()->json($data, $data['code']);
        } else {
            $validate = \Validator::make($params_array, [
                        'title' => 'required',
                        'category_id' => 'required',
                        'content' => 'required'
                        
            ]);
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);

            if ($validate->fails()) {
                return response()->json($data, $data['code']);
                
            } else {
                $user = $this->getIdentity($request);



                //update or create no deja  poner multiples where por eso por array
                $postold = Post::where('id', $id)
                        ->where('user_id', $user->sub)
                        ->first();
       
                $post = Post::where('id', $id)
                        ->where('user_id', $user->sub)
                        ->first();
       
       
                /* $postupdate= Post::where('id',$id)
                  ->where('user_id',$user->sub)->first(); */
                if (is_object($post) && !empty($post)) {
                 
                  
                  
                    $data = array(
                        "status" => "success",
                        "code" => 200,
                        "post" => $postold,
                        "changes" => $params_array
                    );
                    
                    $post->update($params_array);

                     
                } else {
                    $data = [
                        "status" => "errors",
                        "code" => 400,
                        "message" => 'no puede eliminar un registro que no le pertenece',
                    ];
                }
            }
        }
        return response()->json($data, $data['code']);
    }

    public function destroy(Request $request, $id) {

        $user = $this->getIdentity($request);



        $post = Post::where('id', $id)->where('user_id', $user->sub)->first();
        //devuelve un objeto

        if (!empty($post)) {
            $post->delete();
            $data = [
                'status' => 'success',
                'code' => 200,
                'post' => $post
            ];
        } else {
            $data = [
                'status' => 'errors',
                'code' => 400,
                'message' => 'error el post no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }

    private function getIdentity($request) {
        $token = $request->header('Authorization', null);
        $jwt = new JwtAuth();

        $user = $jwt->checkToken($token, true);
        return $user;
    }
    public function  upload(Request $request){
        /*file para recibir un archivo*/
        $image=$request->file('file0');
        $validate= \Validator::make($request->all(),[
            "file0"=>'required|image|mimes:png,jpg,jpeg,gif'
        ]);
        
        if(!$validate->fails() && !empty($image) ){
          
           $image_name= time().$image->getClientOriginalName();
           \Storage::disk('images')->put($image_name,\File::get($image));
           $data=[
                "status"=>"success",
                "code"=>200,
                "image"=>$image_name
            ];
        }else{
            $data=[
                "status"=>"error",
                "code"=>400,
                "message"=>"No se pudo subir correctamente la imagen"
            ];
        }
        return response()->json($data,$data['code']);
    }
    public function  getImage($filename){
  
            $isset=\Storage::disk('images')->exists($filename);


        
        if($isset){
            $image=\Storage::disk('images')->get($filename);
            return Response($image,200);
        }else{
            $data=[
                "status"=>"errors",
                "code"=>400,
                "message"=>"Imagen no encontrada"
            ];
            return response()->json($data,$data['code']);
        }
       
    }
    public function postsByUser($id){
        $post= Post::where('user_id',$id)->get();
        
        return response()->json([
            "status"=>"success",
            "post"=>$post
        ],200);
    }
    public function postsByCategory($id){
        $post=Post::where('category_id',$id)->get();
        return response()->json([
            "status"=>"success",
            "post"=>$post
        ],200);
        
    }
}
