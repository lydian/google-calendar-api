<?
include("http.php");
class GCal{
    var $SID;
    var $LSID;
    var $Auth;
    var $gsessionid;
    var $account;
    var $password;
    function login($account, $password){
        $this->account = $account;
        $this->password = $password;

        $url = "https://www.google.com/accounts/ClientLogin";
        $fields = array(
            'accountType'=>urlencode('GOOGLE'),
            'Email'=>urlencode($account),
            'Passwd'=>urlencode($password),
            'service'=>urlencode('cl'),
            'source'=>urlencode('ntuintellab-pubcal-1.0')
        );
        $r = send_post($url, $fields);
//        if($r->header["status"] != "OK")
//            throw new Exception($r->content);

        $content = explode("\n", $r->content);
        $data = array();
        foreach($content as $val){
            if($val == '') continue;
            $val = explode("=", $val);
            $data[array_shift($val)] = trim(implode("=", $val));
        }
        $this->SID = $data["SID"];
        $this->Auth = $data["Auth"];
        $this->LSID = $data["LSID"];


        //get session_id
        $url = "https://www.google.com/calendar/feeds/default/owncalendars/full?v=2&alt=jsonc";
        $r = $this->sendQuery($url);
        $q = parse_url($r->header["X-Redirect-Location"]);
        parse_str($q["query"], $q);
        $this->gsessionid = $q["gsessionid"];
        

    }
    function sendQuery($url, $get=array(), $post=array(), $header=array(), $user_commend=""){
        $header[] = 'GData-Version: 2';
        $header[] = 'Authorization: GoogleLogin auth='.$this->Auth; 
        $header[] = 'X-If-No-Redirect: 1';
        if($this->gsessionid !='')
            $get = array_merge($get , array("gsessionid"=>$this->gsessionid));

        $q = parse_url($url);
        $url = $q["scheme"]."://". $q["host"].$q["path"];
        if(array_key_exists("query", $q)){
            parse_str($q["query"], $q);
        }else{
            $q = array();
        }
        $url .= "?".http_build_query(array_merge($q, $get));
        return send_post($url, $post, $header, $user_commend);
    }
    function sendPut($url, $get=array(), $data="", $header=array()){
        $header[] = 'GData-Version: 2';
        $header[] = 'Authorization: GoogleLogin auth='.$this->Auth; 
        $header[] = 'X-If-No-Redirect: 1';
        if($this->gsessionid !='')
            $get = array_merge($get , array("gsessionid"=>$this->gsessionid));

        $q = parse_url($url);
        $url = $q["scheme"]."://". $q["host"].$q["path"];
        if(array_key_exists("query", $q)){
            parse_str($q["query"], $q);
        }else{
            $q = array();
        }
        $url .= "?".http_build_query(array_merge($q, $get));
        return send_put($url, $data, $header);

    }
    function getCalendars(){
        $url = "https://www.google.com/calendar/feeds/default/owncalendars/full?v=2&alt=jsonc";
        $r = $this->sendQuery($url);
        $r = json_decode($r->content);
        if($r!=null && property_exists($r, "data") && property_exists($r->data, "items"))
            return $r->data->items;
        return ;
    }
    function addEvent($calendar, $title, $start, $end,  $details="", $location=""){
        $url = $calendar->eventFeedLink;
        $data = json_encode(array("data" => array(
            "title" => $title, 
            "details"=> $start, 
            "transparency"=> $details, 
            "status"=>"confirmed", 
            "location"=>$location, 
            "when"=>array(
                array(
                    "start"=>$start, 
                    "end"=> $end
            )))));
        $r = $this->sendQuery($url , array("alt"=>"jsonc"), $data, array('Content-Type: application/json'));
        $r = json_decode($r->content);
        if($r!=null && property_exists($r, "data"))
            return $r->data;
        return;
    }
    function getEvents($calendar, $start_min="", $start_max="", $q=""){
        $url = $calendar->eventFeedLink;
        $header = array();
        $header[] = "Content-Type: application/json";
        $header[] = "If-Match: *";

        $get=array("alt"=>"jsonc");
        if($start_min !="")
            $get["start-min"] = $start_min;
        if($start_max !="")
            $get["start-max"] = $start_max;
        if($q !="")
            $get["q"] = $q;
        $r = $this->sendQuery($url , $get, array(), $header);
        $r = json_decode($r->content);
        if($r != null && property_exists($r, "data") && property_exists($r->data, "totalResults") && $r->data->totalResults > 0 && property_exists($r->data, "items")){
            return $r->data->items;
        }
        return ;
    }
    function editEvent($old, $new){
        $url = $old->selfLink;
        $header = array();
        $header[] = "Content-Type: application/json";
        $header[] = "If-Match: *";
        unset($new->etag);
        $data = json_encode(array("apiVersion"=>"2.3","data"=> $new));
        $r = $this->sendPut($url , array("alt"=>"jsonc"), $data, $header);
        $r = json_decode($r->content);
        if($r != null && property_exists($r, "data")){
            return $r->data;
        }
        return ;
    }
    function deleteEvent($event){
        $url = $event->selfLink;
        $header = array();
        $header[] = "If-Match: *";
        
        $r = $this->sendQuery($url, array(), array(), $header, "DELETE");
        if(trim($r->header["status"]) == "OK")
            return 1;
        return 0;
        //return json_decode($r->content);
    }

}



?>
