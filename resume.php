<?
// Process and mail the application
if(isset($_POST['pdir'])) {
	// Fixes the paths for Windows
	$workaround = str_replace("|", "\\", $_POST['pdir']);
	$workaround = str_replace("/", "\\", $workaround);
	require 'class.phpmailer.php';

	$mail = new PHPMailer;
	$mail->From = 'noreply@'.$_SERVER['HTTP_HOST'];
	$mail->FromName = 'Application Mailer';
	// Break apart and add any addresses given
	$sendTo = explode(',', $_POST['contact']);
	foreach ($sendTo as $x) {
		$mail->AddAddress($x);
	}
	$mail->WordWrap = 50;
	if($_POST['resattach'] != '' && file_exists($workaround.'uploads\\'.$_POST['resattach'])) {
		$mail->AddAttachment($workaround.'uploads\\'.$_POST['resattach']);
	}
	$mail->IsHTML(true);
	$mail->Subject = 'New Application for '.$_POST['jobtitle'];
	$mail->Body    = 'Hello, a new application for the '.$_POST['jobtitle'].' position has been received! <br><br>
										<table width="100%" style="border: 1px solid #000; border-left: 0; border-radius: 4px; border-spacing: 0;">
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>First Name:</b> '.$_POST['first'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Last Name:</b> '.$_POST['last'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Email Address:</b> '.$_POST['email'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Phone Number:</b> '.$_POST['phone'].'</td>
											</tr>
											<tr>
												<td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Mailing Address:</b> <br> '.$_POST['address'].'</td>
											</tr>
											<tr>
												<td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Education History:</b> <br> '.$_POST['education'].'</td>
											</tr>
											<tr>
												<td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Skills & Certifications:</b> <br> '.$_POST['skills'].'</td>
											</tr>';
											if(isset($_POST['custom1']) && isset($_POST['custom2'])) {
			 $mail->Body .= '<tr>
											   <td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>'.$_POST['custom1'].':</b> <br> '.$_POST['custom2'].'</td>
											 </tr>';
											}
		$mail->Body .= '</table>
										<br>
										If the applicant included a resume in their submission, it has been attached to this email.';
	$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
	if(!$mail->Send()) {
	   echo 'Message could not be sent.';
	   echo 'Mailer Error: ' . $mail->ErrorInfo;
	   exit;
	}
	
	// Send an automatic response if it is requested
	if(isset($_POST['reply'])) {
		$reply = new PHPMailer;
		$reply->From = 'noreply@'.$_SERVER['HTTP_HOST'];
		if(isset($_POST['rname']) && strlen($_POST['rname']) > 0) {
			$reply->FromName = $_POST['rname'];	
		} else {
			$reply->FromName = 'Application Mailer';
		}
		$reply->AddAddress($_POST['email']);
		$reply->WordWrap = 50;
		$reply->IsHTML(true);
		$reply->Subject = 'Application Reception Confirmation';
		$reply->Body    = $_POST['reply'];
		$reply->AltBody = $_POST['reply'];
		if(!$reply->Send()) {
		   echo 'Message could not be sent.';
		   echo 'Mailer Error: ' . $reply->ErrorInfo;
		   exit;
		}
	}
	
	// Remove the uploaded resume
	if ($_POST['resattach'] != '' && file_exists($workaround.'uploads\\'.$_POST['resattach'])) {
		unlink($workaround.'uploads\\'.$_POST['resattach']);
	}
	
	echo '<div class="alert alert-success alert-block">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<h4>Successfully Submitted!</h4>
					Thank you for your application and interest in the position! <br>
					Your application has been successfully submitted and received. It will be reviewed, and we will contact you when we have done so.
				<div>';	
}

// Verify and upload the resume file
if(isset($_FILES['resumefile'])) {
	$filename=str_replace("%", "", $_FILES["resumefile"]["name"]);
	$filename=str_replace("\"", "", $filename);
	$filename=str_replace("'", "", $filename);
	
	if ($_FILES["resumefile"]["type"] != "application/msword" AND $_FILES["resumefile"]["type"] != "application/pdf" AND $_FILES["resumefile"]["type"] != "application/vnd.openxmlformats-officedocument.wordprocessingml.document") {
		echo "Invalid filetype. The file must be a PDF, or a Word document.";
		echo $_FILES["resumefile"]["type"];
		exit();
	} else {
		if (file_exists($filename)) {
			echo $filename . " already exists. ";
		} else {
			move_uploaded_file($_FILES["resumefile"]["tmp_name"], "uploads/" . $filename);
			echo $filename;
		}
	}
}
?>