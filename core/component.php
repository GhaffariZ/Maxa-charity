<?php

function component($name,$data = [])
{

extract($data);

$file = __DIR__."/../components/".$name.".php";

if(file_exists($file)){

include $file;

}

}
