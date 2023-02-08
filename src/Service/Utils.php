<?php
namespace App\Service;

class Utils{
    public function cleanInput($value){
        return htmlspecialchars(strip_tags(trim($value)));
    }
}