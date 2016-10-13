<?php namespace Utilities;

use \PHPMailer; 

class NotificationUtil {
	
	public $db = null;
	
	public $mail = null;
	
	public function __construct( $db = null, $jwtToken, $manifest ) {
	
		$this->db = $db;
		$this->jwtToken = $jwtToken;
		$this->manifest = $manifest;

		//Create a new PHPMailer instance
		$this->mail = new PHPMailer;		
		self::emailConfig();

		//MonoLogger
		$this->logger = new \Monolog\Logger($this->manifest->company->code . '_log');
		$file_handler = new \Monolog\Handler\StreamHandler("logs/smtp_err.log");
		$this->logger->pushHandler($file_handler);		
	}	
		
	//email config
	private function emailConfig() {
		
		//Set character encoding
		$this->mail->CharSet = $this->manifest->smtp->CharSet;		
		
		//Tell PHPMailer to use SMTP
		$this->mail->isSMTP();
		
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$this->mail->SMTPDebug = $this->manifest->smtp->SMTPDebug;
		
		//Ask for HTML-friendly debug output
		$this->mail->Debugoutput = $this->manifest->smtp->Debugoutput;
		
		//Set the hostname of the mail server
		$this->mail->Host = $this->manifest->smtp->Host;
		
		//Set the SMTP port number - likely to be 25, 465 or 587
		$this->mail->Port = $this->manifest->smtp->Port;
		
		//Whether to use SMTP authentication
		$this->mail->SMTPAuth = $this->manifest->smtp->SMTPAuth;
		
		//Secure
		$this->mail->SMTPSecure = $this->manifest->smtp->SMTPSecure;
		
		//Username to use for SMTP authentication
		$this->mail->Username = $this->manifest->smtp->account->Username;
		
		//Password to use for SMTP authentication
		$this->mail->Password = $this->manifest->smtp->account->Password;
		
		//Set who the message is to be sent from
		$this->mail->setFrom(
				$this->manifest->smtp->setFrom->email,
				$this->manifest->smtp->setFrom->name
		);
		
		//Set an alternative reply-to address
		$this->mail->addReplyTo(
				$this->manifest->smtp->addReplyTo->email,
				$this->manifest->smtp->addReplyTo->name
		);				
	}
	
	//email notification for new user
	public function emailUserNew($data) {

		//Set who the message is to be sent to
		//$this->mail->addAddress($data['email'], "$data[first_name] @$data[last_name]");
		$this->mail->addAddress("jmbrothers@gmail.com", "$data[first_name] @$data[last_name]");
		
		//Set the subject line
		$this->mail->Subject = '['.$this->manifest->company->name.'] Email Verification';
		
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body		
		$this->mail->msgHTML(file_get_contents(ROOT_DIR . "/public/email/emailUserNew.html"));
		//$this->mail->Body    = 'This is the HTML message body <b>in bold!</b>';

		//Replace the firsname place holder
		$this->mail->Body = str_replace('[USER_FIRSTNAME]', ucfirst($data['first_name']), $this->mail->Body);						
		
		//Generate email verification
		$isSSL = empty($_SERVER['HTTPS']) ? 'http' : 'https';
		$strEmailVerifiy = $isSSL . '://' . $_SERVER['HTTP_HOST'] . '/user/verify/email?email=dGVzdEB0ZXN0LmNvbQ==&hash=c4ca4238a0b923820dcc509a6f75849b';
		$this->mail->Body = str_replace('[VERIFY_URL]', $strEmailVerifiy, $this->mail->Body);

		//Replace the plain text body with one created manually
		//$this->mail->AltBody = 'This is a plain-text message body';

		//Attach an image file
		//$this->mail->addAttachment('images/phpmailer_mini.png');
		
		//send the message, check for errors
		if (!$this->mail->send()) {
		    $this->logger->addError("Mailer Error: " . $this->mail->ErrorInfo);
		    return false;
		} 
		
		return true;
	}
	
	//send notification new order reciept
	public function emailOrderStatus ($data) {

		//status config
		$arrStatusOrder = (array)$this->manifest->status_order;
		
		//format the order items in TR
		$intSN = 0;
		$strItems = '';
		
		foreach ($data['items'] as $arrItem) {
			$intSN++;
						
			$strAddOns = '';
			if (!empty($arrItem['add_ons'])) {
				
				$arrItemAddOns = $arrItem['add_ons'];
				if (!is_array($arrItem['add_ons']))
					$arrItemAddOns = json_decode($arrItem['add_ons'], true);
					
				foreach ($arrItemAddOns as $arrAddOns) {
					$strAddOns .= $arrAddOns['name'] . ' - ' . $arrAddOns['price'] . '  x ' . $arrAddOns['qty'] . '<br>';			    
				}
			}
			
			$arrItem['price'] = number_format($arrItem['price'],2);
			$arrItem['total_amount'] = number_format($arrItem['total_amount'],2);
			
			$strItems .= <<<EOT
				<tr>
					<td align="center">$intSN</td>
					<td>
						<p>$arrItem[menu_name]</p>
						<p>$strAddOns</p>
					</td>
					<td align="right">$arrItem[price]</td>
					<td align="center">$arrItem[qty]</td>
					<td align="right">$arrItem[total_amount]</td>
				</tr>		
EOT;
		}
		
		//Retrieve vendor name
		$selectStmt = $this->db->select(array('name'))->from('vendors')->where('id','=',$data['transac']['vendor_id']);
		$arrResult = $selectStmt->execute()->fetch();
		$strVendorName = $arrResult['name'];
		
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$this->mail->msgHTML(file_get_contents(ROOT_DIR . "/public/email/emailOrderStatus.html"));
		//$this->mail->Body    = 'This is the HTML message body <b>in bold!</b>';
		
		//Replace the place holders
		$this->mail->Body = str_replace('[USER_FIRSTNAME]', ucfirst($this->jwtToken->user->first_name), $this->mail->Body);
		$this->mail->Body = str_replace('[VENDOR_NAME]', $strVendorName, $this->mail->Body);
		$this->mail->Body = str_replace('[TRANSAC_REF]', $data['transac']['uuid'], $this->mail->Body);
		$this->mail->Body = str_replace('[TRANSAC_DATE]', date('d-M-Y h:iA', strtotime($data['transac']['created'])), $this->mail->Body);
		$this->mail->Body = str_replace('[TRANSAC_PAYMENT_METHOD]', $data['transac']['payment_method'], $this->mail->Body);
		$this->mail->Body = str_replace('[TRANSC_ITEMS]', $strItems, $this->mail->Body);
		$this->mail->Body = str_replace('[TRANSAC_SUBTOTAL]', number_format($data['transac']['sub_total'],2), $this->mail->Body);
		$this->mail->Body = str_replace('[TRANSAC_DISCOUNT]', number_format($data['transac']['discount'],2), $this->mail->Body);
		$this->mail->Body = str_replace('[TRANSAC_DELIVERY_COST]', number_format($data['transac']['delivery_cost'],2), $this->mail->Body);
		$this->mail->Body = str_replace('[TRANSAC_TOTAL_AMOUNT]', number_format($data['transac']['total_amount'],2), $this->mail->Body);
		
		//Set who the message is to be sent to
		$this->mail->addAddress($this->jwtToken->user->email, $this->jwtToken->user->first_name);
		
		//setup proper email subject 
		$strSubject = "Order Status Update";
		if(empty($data['transac']['status']) ) {
			$strSubject = "Order Confirmation";
			$data['transac']['status'] = 1;
		}
		$this->mail->Subject = '['.$strVendorName.'] '.$strSubject.' - Ref: '.$data['transac']['uuid'];		
		
		//set proper order status
		$strStatus = $arrStatusOrder["status_".$data['transac']['status']];
		if ($data['transac']['status'] === 3)
			$strStatus .= " (Payment Ref: ".$data['transac']['payment_ref'].")";
		$this->mail->Body = str_replace('[TRANSAC_ORDER_STATUS]', $strStatus, $this->mail->Body);
		
		//Attach an image file
		//$this->mail->addAttachment('images/phpmailer_mini.png');
		
		//send the message, check for errors
		if (!$this->mail->send()) {
			$this->logger->addError("Mailer Error: " . $this->mail->ErrorInfo);
			return false;
		}
		
		return true;		
		
	}
	
	//Send Order Status Update to Vendor
	//send notification new order reciept
	public function emailOrderStatusVendor ($data) {
	
		//status config
		$arrStatusOrder = (array)$this->manifest->status_order;
	
		//format the order items in TR
		$intSN = 0;
		$strItems = '';
	
		foreach ($data['items'] as $arrItem) {
			$intSN++;
	
			$strAddOns = '';
			if (!empty($arrItem['add_ons'])) {
	
				$arrItemAddOns = $arrItem['add_ons'];
				if (!is_array($arrItem['add_ons']))
					$arrItemAddOns = json_decode($arrItem['add_ons'], true);
					
				foreach ($arrItemAddOns as $arrAddOns) {
					$strAddOns .= $arrAddOns['name'] . ' - ' . $arrAddOns['price'] . '  x ' . $arrAddOns['qty'] . '<br>';
				}
			}
				
			$arrItem['price'] = number_format($arrItem['price'], 2);
			$arrItem['total_amount'] = number_format($arrItem['total_amount'], 2);
				
			$strItems .= <<<EOT
				<tr>
					<td align="center">$intSN</td>
					<td>
						<p>$arrItem[menu_name]</p>
						<p>$strAddOns</p>
					</td>
					<td align="right">$arrItem[price]</td>
					<td align="center">$arrItem[qty]</td>
					<td align="right">$arrItem[total_amount]</td>
				</tr>
EOT;
		}
	
		//Retrieve vendor name
		$selectStmt = $this->db->select(array('name','email'))->from('vendors')->where('id','=',$data['transac']['vendor_id']);
		$arrResult = $selectStmt->execute()->fetch();
		$strVendorName = $arrResult['name'];
		$strVendorEmail = $arrResult['email'];

		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$this->mail->msgHTML(file_get_contents(ROOT_DIR . "/public/email/emailOrderStatusVendor.html"));
				//$this->mail->Body    = 'This is the HTML message body <b>in bold!</b>';

		//Replace the place holders
		$this->mail->Body = str_replace('[TRANSAC_REF]', $data['transac']['uuid'], $this->mail->Body);
		$this->mail->Body = str_replace('[TRANSAC_DATE]', date('d-M-Y h:iA', strtotime($data['transac']['created'])), $this->mail->Body);
		$this->mail->Body = str_replace('[TRANSAC_PAYMENT_METHOD]', $data['transac']['payment_method'], $this->mail->Body);
		$this->mail->Body = str_replace('[TRANSAC_ITEMS]', $strItems, $this->mail->Body);
		$this->mail->Body = str_replace('[TRANSAC_SUBTOTAL]', number_format($data['transac']['sub_total'], 2), $this->mail->Body);
		$this->mail->Body = str_replace('[TRANSAC_DISCOUNT]', number_format($data['transac']['discount'], 2), $this->mail->Body);
		$this->mail->Body = str_replace('[TRANSAC_DELIVERY_COST]', number_format($data['transac']['delivery_cost'], 2), $this->mail->Body);
		$this->mail->Body = str_replace('[TRANSAC_TOTAL_AMOUNT]', number_format($data['transac']['total_amount'], 2), $this->mail->Body);


		//Set who the message is to be sent to
		$this->mail->addAddress($strVendorEmail, $strVendorEmail);

		//setup proper email subject
		$strSubject = "[Order Status Update]";
		if(empty($data['transac']['status']) ) {
			$strSubject = "[New Order]";
			$data['transac']['status'] = 1;
		}
		$this->mail->Subject = $strSubject.' - Ref: '.$data['transac']['uuid'];

		//set proper order status
		$strStatus = $arrStatusOrder["status_".$data['transac']['status']];
		if ($data['transac']['status'] === 3)
			$strStatus .= " (Payment Ref: ".$data['transac']['payment_ref'].")";
		$this->mail->Body = str_replace('[TRANSAC_ORDER_STATUS]', $strStatus, $this->mail->Body);

		//Attach an image file
		//$this->mail->addAttachment('images/phpmailer_mini.png');

		//send the message, check for errors
		if (!$this->mail->send()) {
			$this->logger->addError("Mailer Error: " . $this->mail->ErrorInfo);
			return false;
		}

		return true;
	
	}	
	
	
	public function emailUserResetPassword ($data) {
		//Set who the message is to be sent to
		$this->mail->addAddress("jmbrothers@gmail.com", "$data[first_name] @$data[last_name]");
		
		//Set the subject line
		$this->mail->Subject = '['.$this->manifest->company->name.'] Reset Password';
		
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$this->mail->msgHTML(file_get_contents(ROOT_DIR . "/public/email/emailUserResetPassword.html"));
		
		//Replace the firsname place holder
		$this->mail->Body = str_replace('[USER_FIRSTNAME]', ucfirst($data['first_name']), $this->mail->Body);
		
		//Generate email verification
		$isSSL = empty($_SERVER['HTTPS']) ? 'http' : 'https';
		$strTempPassword = $this->generatePassword();
		$this->mail->Body = str_replace('[TEMP_PASSWORD]', $strTempPassword, $this->mail->Body);
		
		//Replace the plain text body with one created manually
		//$this->mail->AltBody = 'This is a plain-text message body';
		
		//Attach an image file
		//$this->mail->addAttachment('images/phpmailer_mini.png');
		
		var_dump($this->mail->send());die;
		//send the message, check for errors
		if (!$this->mail->send()) {
			$this->logger->addError("Mailer Error: " . $this->mail->ErrorInfo);
			return false;
		}
		
		return true;		
	}
	
	public function generatePassword () {
		 
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
		$pass = array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < 8; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass); //turn the array into a string
		
	}
	
	//send promotions
	
	//send sms verfication
	
}


