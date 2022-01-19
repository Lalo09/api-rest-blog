<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    //asignar nombre tabla en base de datos
    protected $table = 'posts';
    
    //Poder llenar los campos de manera masiva
    protected $fillable = [
        'title', 'content', 'category_id', 'image'];
    
    //Relacion de uno a muchos inversa muchos a uno
    public function user(){
        return $this->belongsTo('App\User','user_id');
    }
    
    public function category(){
        return $this->belongsTo('App\Category','category_id');
    }
}
