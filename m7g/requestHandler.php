<?php
/* Description: Process for Server-Side, Author: Ramesh Singh, Copyright © 2024 PASA */
require_once($_SERVER['DOCUMENT_ROOT'].'/m7g/config.php');
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET': //echo "Handle GET request";
        $param_1 = ""; $value_1 = "";
        if (!empty($_GET)) {
            foreach ($_GET as $name => $value) {
                $param_1 = htmlspecialchars($name);
                $value_1 = htmlspecialchars($value);
            }
        }
        if($param_1 == "content"){
            switch ($value_1) {
                case 'home': 
                    echo "Home page was called"; break;
                case 'detail': 
                    echo "Detail page was called"; break;
                case 'claim': 
                    $m7g->claimContent(); break;
                default: 
                    echo "Error-page"; break;             
            }
        } else if ($param_1 == "var1") {
            switch ($value_1) {
                case 'tags': 
                    $m7g->fetchTags(); break;
                case 'dealer_tags': 
                    $m7g->dealer_fetchTags(); break;
                case 'list':
                    $m7g->fetchList(); break;
                default: 
                    echo "var1 undefined"; break;        
            }
        } else if ($param_1 == "itemid") {
            $m7g->fetchItem($value_1);            
        } else if ($param_1 == "itemdet") {
            $m7g->fetchDetail($value_1);   
        } else { 
            echo "No GET parameters found.";
        }
        break;
    case 'POST': //echo "Handle POST request";
        $_POST = array_merge($_POST, $_GET); // caution!!
        $opCode = isset($_POST['opCode']) ? $_POST['opCode'] : '';
        //echo "POST-opCode=".$opCode;
        switch ($opCode) {
            case 'newClaim': 
                $m7g->exeClaim(); break;
            case 'newVin': 
                $m7g->exeClaim(); break;
            case 'login':
                //echo "Method Not Allowed"; break; 
                $m7g->exeLogin(); break;
            case 'register': 
                $m7g->exeRegister(); break;
            case 'addTag': // from the $_GET array
                $m7g->storeTag(); 
                break;
            default: 
                echo "Method Not Allowed"; break;         
        }
        break;
    default: // echo "Handle unknown request";
        header("HTTP/1.1 405 Method Not Allowed");
        echo "Method Not Allowed"; break;
}
?>
