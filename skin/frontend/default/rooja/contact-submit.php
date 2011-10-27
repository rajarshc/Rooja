<?php
    error_reporting(E_NOTICE);
 	function clean_var($variable) {
    	$variable = strip_tags(stripslashes(trim(rtrim($variable))));
 		 return $variable;
	}
    function valid_email($str)
    {
        return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
    }
 
    if($_POST['email']!='' && valid_email($_POST['email'])==TRUE && $_POST['yourName']!='')
    {
        $to = "iam@emersontaymor.com";
        $headers =  'From: '. clean_var($_POST['contactEmail']) .''. "\r\n" .
                'Reply-To: '. clean_var($_POST['contactEmail']) .'' . "\r\n" .
        $subject = "[Rooja] ". clean_var($_POST['subject']);
        $message = "Name: " . clean_var($_POST['yourName']) . "\n";
		$message .= "Email: " . clean_var($_POST['email']) . "\n";
	    $message .= "Message: \n" . clean_var($_POST['message']);
       
        if(mail($to, $subject, $message, $headers)){
			header( "Location: http://rooja.com/success" );
        }
        else {
            header( "Location: http://rooja.com/error" );
        }
    }
    else {
         header( "Location: http://rooja.com/error" );
    }
?>