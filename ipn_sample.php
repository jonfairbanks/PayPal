<?php

	// Read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-validate';
	foreach ($_POST as $key => $value) {
		$value = urlencode(stripslashes($value));
		$req .= "&$key=$value";
	}

	// Post back to PayPal system to validate
	$header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
	$header .= "Host: www.sandbox.paypal.com\r\n";
	//$header .= "Host: www.paypal.com\r\n"; // Enable for Live
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n";
	$header .= "Connection: close\r\n\r\n";
	$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
	//$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30); // Enable for Live

	// assign posted variables to local variables
	$item_name = $_POST['item_name'];
	$item_number = $_POST['item_number'];
	$payment_status = $_POST['payment_status'];
	$payment_amount = $_POST['mc_gross'];
	$payment_currency = $_POST['mc_currency'];
	$txn_id = $_POST['txn_id'];
	$receiver_email = $_POST['receiver_email'];
	$payer_email = $_POST['payer_email'];

	if (!$fp) {
		// HTTP ERROR
	} else {
		fputs ($fp, $header . $req);
		while (!feof($fp)) {
			$res = fgets ($fp, 1024);
			
			if (strcmp ($res, "VERIFIED") == 0) {
				$mail_From = "From: ipn-listener@paypaltech.com"; // Feel free to change this value. This will show where the emails are coming from. 
				$mail_To = "IPN-TEST@paypal.com"; // Please declare your email here!
				$mail_Subject = "VERIFIED IPN";
				$mail_Body = $req;

				foreach ($_POST as $key => $value){
					$emailtext .= $key . " = " .$value ."\n\n";
				}

				mail($mail_To, $mail_Subject, $emailtext . "\n\n" . $mail_Body, $mail_From);

			}
			else if (strcmp ($res, "INVALID") == 0) {
				$mail_From = "From: ipn-listener@paypaltech.com"; // Feel free to change this value. This will show where the emails are coming from. 
				$mail_To = "IPN-TEST@paypal.com"; // Please declare your email here!
				$mail_Subject = "INVALID IPN";
				$mail_Body = $req;

				foreach ($_POST as $key => $value){
					$emailtext .= $key . " = " .$value ."\n\n";
				}

				mail($mail_To, $mail_Subject, $emailtext . "\n\n" . $mail_Body, $mail_From);

			}
		}
		fclose ($fp);
	}
	
?>