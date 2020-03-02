<?php

namespace App\Http\Controllers;
use App\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
class CategoryController extends Controller
{
    public function __construct() {
        $this->middleware('api.auth',['except'=>['index','show']]);
    }
      public function pruebas(Request $request){
        return "accion de pruebas CATEGORY controller";
    }
    public function index(){
        $categories=Category::all();
        
        return response()->json([
            'code'=>400,
            'status'=>'success',
            'categories'=>$categories
            ]);
    }
    public function show($id){
        $categorie= Category::find($id);
        if(is_object($categorie)){
            $data=array(
                'status'=>'success',
                'code'=>200,
                'categories'=>$categorie
            );
        }else{
              $data=array(
                'status'=>'errors',
                'code'=>400,
                'message'=>'categoria no encontrada'
            );
        }
        return response()->json($data,$data['code']);
    }
    public function store(Request $request){
        $json=$request->input('json',null);
        $params_array= json_decode($json,true);
        
        if(!empty($params_array)){
        $validate=\Validator::make($params_array,[
            'name'=>'required'
        ]);
        
        if($validate->fails()){
            $data=array(
                'status'=>'errors',
                'code'=>400,
                'message'=>'no se ha guardado la categoria'
            );
        }else{
            $category=new Category();
            $category->name=$params_array['name'];
            $category->save();
            $data=array(
                  'status'=>'success',
                'code'=>200,
                'categorie'=>$params_array
            );
        }
        }else{
             $data=array(
                'status'=>'errors',
                'code'=>400,
                'message'=>'datos vacios'
            );
        }
        return response()->json($data,$data['code']);
        
    }
    
    public function update($id,Request $request){
        $json=$request->input('json',null);
        $params_array= json_decode($json,true);
    
        if(!empty($params_array)){
            $validated= \Validator::make($params_array,[
                'name'=>'required'
            ]);
            
          
            unset($params_array['id']);
            unset($params_array['created_at']);
  
            $category= Category::where('id',$id)->update($params_array);
             $data=array(
                'status'=>'success',
                'code'=>200,
                'category'=>$params_array
            );
        }else{
            $data=array(
                'status'=>'errors',
                'code'=>400,
                'message'=>'Categoria Vacia'
            );
        }
        return response()->json($data,$data['code']);
        
        
    }
}
