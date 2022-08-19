<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reporter extends Model
{
    use HasFactory;
    public string $key;
    public string $inn;
    public function returndata($key, $inn){
        $url = 'https://focus-api.kontur.ru/api3/req';
        $options = array(
            'key' => $key,
            'inn' => $inn
        );
        $adres = $url.'?'.http_build_query($options);
        $response = file_get_contents($adres);
        $data = json_decode($response, true);
        return $data;
    }
    public string $text;
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
