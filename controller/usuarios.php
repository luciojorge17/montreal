<?php
include_once '../config/database.php';
$action = $_POST['action'];
switch ($action) {
    case 'verificaPermissaoDesconto':
        session_start();
        $idUsuario = $_SESSION['id_usuario'];
        $retorno = 0;
        $select = "SELECT TOP(1) X_AUTORIZA_DESCONTO FROM TBL_USUARIOS WHERE CD_CODUSUARIO=$idUsuario";
        $consulta = odbc_exec($conexao, $select);
        if(odbc_num_rows($consulta) > 0){
            while(odbc_fetch_row($consulta)){
                $retorno = odbc_result($consulta, "x_autoriza_desconto");
            }
        }
        echo json_encode($retorno);
        break;
    default:
        echo 'Ação não encontrada';
        break;
}
