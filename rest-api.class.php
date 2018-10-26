<?php

class Bitrix{
    private $app_id = "local.You-id";
    private $app_secret_code = "You-Secret-Code";
    private $app_reg_url = "You-url-app";
    private $type_api = "rest"; //Type method api
    private $code_webhook = "You-webhook";
    private $data_type_request = "json";//You type request data 
    private $id_user_webhook = "Id-user-request"; 
    private $id_active_manager = "Manager";
    
    function url($app_reg_url, $type_api, $id_user_webhook, $code_webhook, $method, $data_type_request){
        return $app_reg_url."/".$type_api."/".$id_user_webhook."/".$code_webhook."/".$method.".".$data_type_request;
    }
    
    public function send($method, $queryData, $mes_success){
        if( $curl = curl_init() ) {
            curl_setopt($curl, CURLOPT_URL, $this->url($this->app_reg_url, $this->type_api, $this->id_user_webhook, $this->code_webhook, $method, $this->data_type_request));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($queryData));
            $result = curl_exec($curl);
            curl_close($curl);
            if ($result != '') {
                $this->notify($mes_success);
                return json_decode($result);
            }else{
                return "Результат пусто";
            }
        }
    }
     
    public function notify($message){
        $method = "im.notify";
        $queryData = array(
            'to' => [1, 13],
            'message' => $message,
            'type' => 'SYSTEM'
        );
        if ($message !== ""){
            return $this->send($method, $queryData, '');
        }
    }
    
    public function add_lead(){
        $method = "crm.lead.add";
        $queryData = array(
            'fields' => array(
                "TITLE" => $_POST['Name']." - "."Индивидуальный проект",
                "NAME" => $_POST['Name'],
                "STATUS_ID" => "NEW",
                "OPENED" => "Y",
                "ASSIGNED_BY_ID" => $this->id_active_manager,
                "PHONE" => array(array("VALUE" => $_POST['Phone'], "VALUE_TYPE"=> "WORK" )),
                "EMAIL" => array(array("VALUE" => $_POST['Email'], "VALUE_TYPE"=> "WORK" )),
                "DATE_CREATE" =>  date('d.m.o'),
                "SOURCE_DESCRIPTION" => "Площадь: ".$_POST['Ploshad']." кв.м."."\n".
                                        "Количество комнат: ".$_POST['Komnatu']." шт."."\n".
                                        "Количество этажей: ".$_POST['Etazu']." шт."."\n".
                                        "Количество сан. узлов: ".$_POST['Sanuzel']." шт."."\n".
                                        "Наличие террасы: ".$_POST['Terrasa']."\n".
                                        "Наличие балкона: ".$_POST['Balkon']."\n".
                                        "Технология строительства: ".$_POST['Type']."\n".
                                        "Тип проживания: ".$_POST['Type_p']
            ),
            'params' => array("REGISTER_SONET_EVENT" => "Y")
        );
        return $this->send($method, $queryData, "Новая заявка на Арт-Строй");
    }
     
    public function update_user(){
        $method = "crm.lead.update";
        $queryData = array(
            'id' => intval($_POST["Id"]),
            'fields' => array(
                "PHONE" => array(array("VALUE" => $_POST["Phone"], "VALUE_TYPE" => "WORK"))
            ),
            'params' => array("REGISTER_SONET_EVENT" => "Y")
        );
        return $this->send($method, $queryData, "Обновились данные у заявки на Арт-Строй");
    }
}

$bitrix = new Bitrix;

if (!isset($_POST["Phone"]) && $_POST["Phone"] == ""){
        return json_encode($bitrix->add_lead());
}else{
        return json_encode($bitrix->update_user());
}