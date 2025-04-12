<?php

class paypal_ipn
{
	var $paypal_post_vars;
	var $paypal_response;
	var $timeout;

	var $error_email;
	
	function paypal_ipn($paypal_post_vars) {
		$this->paypal_post_vars = $paypal_post_vars;
		$this->timeout = 120;
	}

	function send_response()
	{
		//$fp = @fsockopen( "www.paypal.com", 80, &$errno, &$errstr, 120 ); 
    $fp = @fsockopen( "ssl://" . PAYPALSITE, 443, $errno, $errstr, 30 ); 

		if (!$fp) { 
			$this->error_out("PHP fsockopen() error: " . $errstr , "");
		} else {
			$response = "cmd=_notify-validate";
			foreach($this->paypal_post_vars AS $key => $value) {
				$response .= "&$key=" . urlencode(stripslashes($value));
			}

			fputs( $fp, "POST /cgi-bin/webscr HTTP/1.0\r\n" ); 
			fputs( $fp, "Content-type: application/x-www-form-urlencoded\r\n" ); 
      fputs( $fp, "Host: " . PAYPALSITE . "\r\n" ); 
			fputs( $fp, "Content-length: " . strlen($response) . "\r\n\r\n" ); 
			fputs( $fp, "$response\n\r" ); 
			fputs( $fp, "\r\n" );

			$this->send_time = time();
			$this->paypal_response = ""; 

			// get response from paypal
			while (!feof($fp)) { 
				$this->paypal_response .= fgets( $fp, 1024 ); 

				if ($this->send_time < time() - $this->timeout) {
					$this->error_out("Timed out waiting for a response from PayPal. ($this->timeout seconds)" , "");
				}
			}

			fclose( $fp );

		}

	}
	
	function is_verified() {
		if( preg_match("/VERIFIED/", $this->paypal_response) )
			return true;
		else
			return false;
	} 

	function get_payment_status() {
		return $this->paypal_post_vars['payment_status'];
	}

	function error_out($message, $em_headers)
	{
		$date = date("D M j G:i:s T Y", time());
		$message .= "\n\nThe following data was received from PayPal:\n\n";

		@reset($this->paypal_post_vars);
		while( @list($key,$value) = @each($this->paypal_post_vars)) {
			$message .= $key . ':' . " \t$value\n";
		}
		mail($this->error_email, "[$date] paypay_ipn notification", $message, $em_headers);
    $fh = fopen('debug.log',a);
    fwrite($fh, "\n\n[$date] paypay_ipn notification\n\n" . $message . "\n\n" . $em_headers);
    fclose($fh);
	}
} 

?>