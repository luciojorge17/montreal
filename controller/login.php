<?php
    session_start();
    include_once '../config/database.php';
    $usuario = strtoupper($_POST['login']);
    $senha = $_POST['senha'];
    $retorno = [];

    $select = "SELECT TOP(1) CD_CODUSUARIO, DS_USUARIO, DS_LOGIN, DS_SENHA FROM TBL_USUARIOS WHERE DS_LOGIN='$usuario' AND X_ATIVO=1";
    $consulta = odbc_exec($conexao, $select);
    if(odbc_num_rows($consulta) > 0){
        while(odbc_fetch_row($consulta)){
            $temp = odbc_result($consulta, "ds_senha");
            if($senha === $temp){
                $retorno['autorizado'] = 1;
                $idUsuario = odbc_result($consulta, "cd_codusuario");
                $nomeUsuario = utf8_encode(odbc_result($consulta, "ds_login"));
                $_SESSION['nome_usuario'] = $nomeUsuario;
                $_SESSION['id_usuario'] = $idUsuario;
            } else{
                $retorno['autorizado'] = 0;
                $retorno['mensagem'] = 'Senha incorreta';
            }
        }
    } else{
        $retorno['autorizado'] = 0;
        $retorno['mensagem'] = 'Usuário não encontrado';
    }

    echo json_encode($retorno);
    
?>