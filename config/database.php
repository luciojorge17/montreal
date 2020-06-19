<?php
    $ip_conexao = 'localhost\SQLEXPRESS';
    $database = 'MONTREAL';
    $usuario = 'sa';
    $senha = '0215@aaws';  
    try{
        $conexao = odbc_connect("Driver={SQL Server Native Client 11.0};Server=$ip_conexao;charset=UTF-8;Database=$database", $usuario, $senha);
    }catch(Exception $e){
        echo $e->getMessage();
        exit;
    }
?>