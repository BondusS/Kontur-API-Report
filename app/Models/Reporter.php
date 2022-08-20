<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reporter{
    /*use HasFactory;*/
    public string $key;
    public string $inn;
    public string $text;
    public function __construct(string $key, string $inn){
        $this->key = $key;
        $this->inn = $inn;
    }
    public function returndata(){
        $url = 'https://focus-api.kontur.ru/api3/req';
        $options = array(
            'key' => $this->key,
            'inn' => $this->inn
        );
        $adres = $url.'?'.http_build_query($options);
        $response = file_get_contents($adres);
        $data = json_decode($response, true);
        return $data;
    }
    public function printing($data){
        foreach($data as $key => $val){
            if(gettype($val) == 'string' 
                or gettype($val) == 'boolean'
                or gettype($val) == 'integer'){
                $this->text .= $key.': '.$val.'|';
            }
            else{
                $this->printing($val);
            }
        }
    }
}
