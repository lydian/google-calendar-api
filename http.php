<?
function send_post($url, $post, $header=array(), $user_commend=""){ 
    //url-ify the data for the POST
    $post_string = "";
    if(is_array($post)){
        foreach($post as $key=>$value) { 
            $post_string .= $key.'='.$value.'&'; 
        }   
        rtrim($post_string,'&');
        $count_post = count($post);
    }
    else{
        $post_string = $post;
        $count_post = 1;
    }

    $ch = curl_init($url);
    if(preg_match("/^https:\/\/.*/", $url)){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    if(count($post)>0){
        curl_setopt($ch,CURLOPT_POST, $count_post);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
    }
    if($user_commend != ""){
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $user_commend);
    }
    curl_setopt($ch, CURLOPT_HEADER, true);  // DO NOT RETURN HTTP HEADERS 
    //curl_setopt($ch, CURLOPT_NOBODY, true);  // DO NOT RETURN HTTP HEADERS 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // RETURN THE CONTENTS OF THE CALL
    if(count($header)>0){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    $Rec_Data = curl_exec($ch);
    curl_close($ch);
    return parse_result($Rec_Data);
}
function parse_result($Rec_Data){
    $header = explode("\r\n\r\n", $Rec_Data);

    $tempheader = explode("\n",array_shift($header));
    $content = implode("\r\n\r\n", $header);
    $header = array();
    $header["status"] = explode(" ", $tempheader[0]);
    $header["protocol"] = array_shift($header["status"]);
    $header["return_code"] = array_shift($header["status"]);
    $header["status"] = implode(" ", $header["status"]);
    for($i=1; $i< count($tempheader); $i++){
        $temp =  explode(":", $tempheader[$i]);
        $key = trim(array_shift($temp));
        $header[$key] = trim(implode(":",$temp));
    }
    return (object)array("header"=>$header, "content"=>$content);
}
function send_put($url , $data, $header){
    $length = strlen($data);
    $fh = fopen('php://memory', 'rw');
    fwrite($fh, $data);
    rewind($fh);

    $ch = curl_init($url);
    if(preg_match("/^https:\/\/.*/", $url)){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    if(count($header)>0){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    curl_setopt($ch, CURLOPT_HEADER, true);  // DO NOT RETURN HTTP HEADERS 
    curl_setopt($ch, CURLOPT_INFILE, $fh);
    curl_setopt($ch, CURLOPT_INFILESIZE, $length);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_PUT, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    fclose($fh);
    return parse_result($result);
}
?>
