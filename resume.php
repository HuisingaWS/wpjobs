<?
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
	exit();
}

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
											</tr>';
			if(isset($_POST['custom1']) && isset($_POST['custom2'])) {
			 $mail->Body .= '<tr>
											   <td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>'.$_POST['custom1'].':</b> <br> '.$_POST['custom2'].'</td>
											 </tr>';
											}
								 $mail->Body .= '
								 			<tr><td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000; background-color: #cccccc;"><b>Further Information</b></td></tr>
								 			<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Date Available:</b> '.$_POST['available'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Desired Salary:</b> '.$_POST['salary'].'</td>
											</tr>
								 			<tr>
												<td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Experience & Knowledge:</b> <br> '.$_POST['experience'].'</td>
											</tr>
											<tr>
												<td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Special Skills:</b> <br> '.$_POST['skills'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>US Citizen:</b> '.$_POST['citizen'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>If no, authorized?:</b> '.$_POST['authorized'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Willing to Relocate?:</b> '.$_POST['relocate'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>If yes, explain:</b> '.$_POST['relocate2'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Worked for us before?:</b> '.$_POST['previous'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>If so, which and when?:</b> '.$_POST['previous2'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Convicted of a Felony?:</b> '.$_POST['felony'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>If yes, explain:</b> '.$_POST['felony2'].'</td>
											</tr>';
											
									if(isset($_POST['hs']) && strlen($_POST['hs']) > 1) {
										$mail->Body .= '
								 			<tr><td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000; background-color: #cccccc;"><b>Education History</b></td></tr>
								 			<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>High School:</b> '.$_POST['hs'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>City, State:</b> '.$_POST['hs2'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>From - To:</b> '.$_POST['hs3'].' - '.$_POST['hs4'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Did you graduate?:</b> '.$_POST['hs5'].'</td>
											</tr>';
										if(isset($_POST['c11']) && strlen($_POST['c11']) > 1) {
											$mail->Body .= '
								 			<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>College:</b> '.$_POST['c11'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>City, State:</b> '.$_POST['c12'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>From:</b> '.$_POST['c13'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>To:</b> '.$_POST['c14'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Did you graduate?:</b> '.$_POST['c15'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Degree:</b> '.$_POST['c16'].'</td>
											</tr>';
											
										}
										if(isset($_POST['c21']) && strlen($_POST['c21']) > 1) {
											$mail->Body .= '
								 			<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>College:</b> '.$_POST['c21'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>City, State:</b> '.$_POST['c22'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>From:</b> '.$_POST['c23'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>To:</b> '.$_POST['c24'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Did you graduate?:</b> '.$_POST['c25'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Degree:</b> '.$_POST['c26'].'</td>
											</tr>';
										}
										$mail->Body .= '
								 			<tr>
												<td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Career Objectives:</b> '.$_POST['objectives'].'</td>
											</tr>
											<tr>
												<td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Special Training, Experience, or Pertinent Data:</b> '.$_POST['etc'].'</td>
											</tr>
											<tr>
												<td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>How Did You Hear About Us?:</b> '.$_POST['referral'].'</td>
											</tr>';
									}
									
									if(isset($_POST['branch']) && strlen($_POST['branch']) > 1) {
										$mail->Body .= '
								 			<tr><td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000; background-color: #cccccc;"><b>Military Service</b></td></tr>
								 			<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Branch:</b> '.$_POST['branch'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>From - To:</b> '.$_POST['mi1'].' - '.$_POST['mi2'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Rank at Discharge:</b> '.$_POST['mi3'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Type of Discharge:</b> '.$_POST['mi4'].'</td>
											</tr>
											<tr>
												<td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>If other than Honorable, Explain:</b> '.$_POST['mi5'].'</td>
											</tr>';
									}
									
									if(isset($_POST['peco1']) && strlen($_POST['peco1']) > 1) {
										$mail->Body .= '
								 			<tr><td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000; background-color: #cccccc;"><b>Previous Employment</b></td></tr>
								 			<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Company:</b> '.$_POST['peco1'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Address:</b> '.$_POST['pead1'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Job Title:</b> '.$_POST['pejt1'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Phone:</b> '.$_POST['peph1'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Supervisor:</b> '.$_POST['pesu1'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Can Contact for Reference?:</b> '.$_POST['peref1'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Starting Salary:</b> '.$_POST['pess1'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Ending Salary:</b> '.$_POST['pees1'].'</td>
											</tr>
											<tr>
												<td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Responsibilities:</b> '.$_POST['peres1'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>From - To:</b> '.$_POST['pefr1'].' - '.$_POST['peto1'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Reason for Leaving:</b> '.$_POST['perl1'].'</td>
											</tr>';
											if(isset($_POST['peco2']) && strlen($_POST['peco2']) > 1) {
												$mail->Body .= '
								 			<tr><td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Previous Employment</b></td></tr>
								 			<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Company:</b> '.$_POST['peco2'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Address:</b> '.$_POST['pead2'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Job Title:</b> '.$_POST['pejt2'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Phone:</b> '.$_POST['peph2'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Supervisor:</b> '.$_POST['pesu2'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Can Contact for Reference?:</b> '.$_POST['peref2'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Starting Salary:</b> '.$_POST['pess2'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Ending Salary:</b> '.$_POST['pees2'].'</td>
											</tr>
											<tr>
												<td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Responsibilities:</b> '.$_POST['peres2'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>From - To:</b> '.$_POST['pefr2'].' - '.$_POST['peto2'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Reason for Leaving:</b> '.$_POST['perl2'].'</td>
											</tr>';
											}
											if(isset($_POST['peco3']) && strlen($_POST['peco3']) > 1) {
												$mail->Body .= '
								 			<tr><td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Previous Employment</b></td></tr>
								 			<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Company:</b> '.$_POST['peco3'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Address:</b> '.$_POST['pead3'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Job Title:</b> '.$_POST['pejt3'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Phone:</b> '.$_POST['peph3'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Supervisor:</b> '.$_POST['pesu3'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Can Contact for Reference?:</b> '.$_POST['peref3'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Starting Salary:</b> '.$_POST['pess3'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Ending Salary:</b> '.$_POST['pees3'].'</td>
											</tr>
											<tr>
												<td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Responsibilities:</b> '.$_POST['peres3'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>From - To:</b> '.$_POST['pefr3'].' - '.$_POST['peto3'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Reason for Leaving:</b> '.$_POST['perl3'].'</td>
											</tr>';
											}
											if(isset($_POST['peco4']) && strlen($_POST['peco4']) > 1) {
												$mail->Body .= '
								 			<tr><td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Previous Employment</b></td></tr>
								 			<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Company:</b> '.$_POST['peco4'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Address:</b> '.$_POST['pead4'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Job Title:</b> '.$_POST['pejt4'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Phone:</b> '.$_POST['peph4'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Supervisor:</b> '.$_POST['pesu4'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Can Contact for Reference?:</b> '.$_POST['peref4'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Starting Salary:</b> '.$_POST['pess4'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Ending Salary:</b> '.$_POST['pees4'].'</td>
											</tr>
											<tr>
												<td colspan="2" style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Responsibilities:</b> '.$_POST['peres4'].'</td>
											</tr>
											<tr>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>From - To:</b> '.$_POST['pefr4'].' - '.$_POST['peto4'].'</td>
												<td style="border-left: 1px solid #000; padding: 8px; line-height: 20px; text-align: left; vertical-align: top; border-top: 1px solid #000;"><b>Reason for Leaving:</b> '.$_POST['perl4'].'</td>
											</tr>';
											}
									}
											
		$mail->Body .= '</table>
										<br>
										If the applicant included a resume in their submission, it has been attached to this email.';
	$mail->AltBody = 'This message requires HTML to be enabled to view it properly.';
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
?>