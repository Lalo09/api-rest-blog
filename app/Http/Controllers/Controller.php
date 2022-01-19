<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Post;
use App\Category;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    //Probar los ORM
    public function testOrm(){
        $posts = Post::all();
        //var_dump($post);
        foreach ($posts as $post){
            echo "<h1>".$post->title."</h1>";
            echo "<span>".$post->user->name."-".$post->category->name."</span>";//Extraer datos relacionados
            echo "<p>".$post->content."</p>";
        }
        
        die();
    }
}
