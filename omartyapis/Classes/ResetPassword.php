<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
    require '../../vendor/autoload.php';
    include("../../Config.php");
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    header("content-type: Application/json");
    
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
 
    //Load Composer's autoloader

    // $method = $_GET["method"];

class ResetPassword extends Functions
{
    function sendmailTRP()  /* send mail to reset password method */
    {
        
        include("../../Config.php");
        $mail = new PHPMailer(true);
        $UserEmail = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'MuhammadWaheed73780@gmail.com';                     //SMTP username
            $mail->Password   = 'nxuoyvgrpgfvkrvh';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 785;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('MuhammadWaheed73780@gmail.com', 'Omarty Super Admin');
            $mail->addAddress($UserEmail, "Omarty User");     //Add a recipient
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = "Reset Password";
            $mail->Body    = "You are receiving this email to <html><a href= 'https://kcgserver.com/omarty/changePassword.html'>reset your password</a></html> if it's not you please secure your acount." ;
           
            $sqlScheckEmail = $conn->query("SELECT ID FROM Resident_User Where Email = '$UserEmail'");
            if($sqlScheckEmail->num_rows > 0)
            {
                $mail->send();
                // $this->returnResponse(200, "Mail Sent.");
                $secret = "secret123";
                $payload = [
                    'iat' => time(),
                    'exp' => time()+(50*60),
                    'email' => $UserEmail
                ];
                $token = JWT::encode($payload, $secret, "HS256");
                $this->returnResponse(200, "OK"); // $token
            }
            elseif($sqlScheckEmail->num_rows <= 0)
            {
                $this->throwError(200, "Email Not found in Our DB.");
            }
            // echo 'Message has been sent';
        } 
        catch (Exception $e) {
            $this->throwError(100, "Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }

    }

    function changePassword()
    {

        include("../../Config.php");

            
        $password = $_POST["newPassword"];
        $confirmPassword = $_POST["confirmPassword"];
        $email = $_POST["email"];
        
        // $token = $this->getBearerToken();
        // $decode = JWT::decode($token, new Key("secret123", "HS256"));

        // $email = $decode->email;
        if(empty($email))
        {
            // header("Location: localhost/Omarty/changePassword.html?error=Please enter your Email.");
            // header("Location: https://kcgserver.com/omarty/changePassword.html?error=Please enter your Email.");
            $this->throwError(100, "Please enter Email.");
        }
        if(!empty($email))
        {
            $sqlGetEmail = $conn->query("SELECT Email FROM Resident_User WHERE Email = '$email'");
            if($sqlGetEmail->num_rows <= 0)
            {
                // header("Location: localhost/Omarty/changePassword.html?error=Email not found.");
                // header("Location: https://kcgserver.com/omarty/changePassword.html?error=Email not found.");
                $this->throwError(200, "Email Not Found.");

            }
            else
            {
                if(empty($password) && empty($confirmPassword))
                {
                    // header("Location: localhost/Omarty/changePassword.html?error=Please complete your Data.");
                    // header("Location: https://kcgserver.com/omarty/changePassword.html?error=Please complete your Data.");
                    $this->throwError(200, "Please enter your new password and confirm it.");
                }
                elseif(empty($password) || empty($confirmPassword))
                {
                    // header("Location: localhost/Omarty/changePassword.html?error=Please complete your data.");
                    // header("Location: https://kcgserver.com/omarty/changePassword.html?error=Please complete your data.");
                    $this->throwError(200, "Please enter your new password and confirm it.");

                }
                elseif(isset($password) && isset($confirmPassword))
                {
                    
                    if($password === $confirmPassword)
                    {
                        $password = password_hash($password, PASSWORD_BCRYPT);
                        $sql = $conn->query("UPDATE Resident_User SET Password = '$password' WHERE Email = '$email' ");
                        if($sql)
                        {
                            $this->returnResponse(200, "Password Changed.");
                            
                        }
                        else
                        {
                            $this->returnResponse(200, "Password not Changed.");
                        }
                    }
                    elseif($password != $confirmPassword)
                    {
                        // header("Location: localhost/Omarty/changePassword.html?error=Passwords dont match.");
                        // header("Location: https://kcgserver.com/omarty/changePassword.html?error=Passwords dont match.");
                        $this->throwError(200, "passwords don't match.");

                    }
                }
            }
        }    
    }

    function sendmailOTP()
    {
        include("../../Config.php");
        $mail = new PHPMailer(true);
        $UserEmail = filter_var($_POST["email"],FILTER_SANITIZE_EMAIL);
        // $OTP = $_POST["otp"];

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'ssl://smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'MuhammadWaheed73780@gmail.com';                     //SMTP username
            $mail->Password   = 'nxuoyvgrpgfvkrvh';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    // 465 TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            $OTP = rand(1,1000000);
            //Recipients
            $mail->setFrom('MuhammadWaheed73780@gmail.com', 'Omarty Super Admin');
            $mail->addAddress($UserEmail, "Omarty User");     //Add a recipient
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = "Reset Password";
            $mail->Body    = "You are receiving this email to Reset your password. Your verification Number is $OTP" ;

            $sqlScheckEmail = $conn->query("SELECT Email FROM Resident_User Where Email = '$UserEmail'");
            if($sqlScheckEmail->num_rows > 0)
            {
                // insert OTP to Users record.
                $sqlInsertOtp = $conn->query("UPDATE Resident_User SET OTP = '$OTP' WHERE Email = '$UserEmail'");
                $mail->send();
                // $this->returnResponse(200, "Mail Sent.");
                $secret = "secret123";
                $payload = [
                    'iat' => time(),
                    'exp' => time()+(50*60),
                    'email' => $UserEmail
                ];
                $token = JWT::encode($payload, $secret, "HS256");
                $this->returnResponse(200, $token);
                // print_r($token);
            }
            elseif($sqlScheckEmail <= 0)
            {
                $this->throwError(200, "Email Not found in Our DB.");
                // print_r("Email Not found in Our DB.");
            }
            // echo 'Message has been sent';
        } 
        catch (Exception $e) {
            $this->throwError(200, "Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            // print_r("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }

    }

    function changePasswordOTP()
    {

        include("../../Config.php");
        
        $email = $_POST["email"];
        $OTP = $_POST["otp"];
        if(empty($email))
        {
            $this->throwError(100, "Please enter Email.");
        }
        if(!empty($email))
        {
            $sqlGetEmail = $conn->query("SELECT Email FROM Resident_User WHERE Email = '$email'");
            if($sqlGetEmail->num_rows <= 0)
            {
                $this->throwError(200, "Email Not Found.");
            }
            else
            {
                if(!empty($OTP))
                {
                    // Check OTP in Data base Table Resident_User WHERE Email = $email.
                    $sqlCheckOTP = $conn->query("SELECT OTP FROM Resident_User WHERE Email = '$email'");
                    if($sqlCheckOTP->num_rows > 0)
                    {
                        $DBOTP = $sqlCheckOTP->fetch_row();
                        if($OTP == $DBOTP[0])
                        {
                            $password = $_POST["newPassword"];
                            $confirmPassword = $_POST["confirmPassword"];
                            
                            if(empty($password) && empty($confirmPassword))
                            {
                                $this->throwError(200, "Please enter your new password and confirm it.");
                            }
                            elseif(empty($password) || empty($confirmPassword))
                            {
                                $this->throwError(200, "Please enter your new password and confirm it.");
            
                            }
                            elseif(isset($password) && isset($confirmPassword))
                            {
                                
                                if($password === $confirmPassword)
                                {
                                    $password = password_hash($password, PASSWORD_BCRYPT);
                                    $sql = $conn->query("UPDATE Resident_User SET Password = '$password' WHERE Email = '$email' ");
                                    if($sql)
                                    {
                                        // Delete OTP From record.
                                        $sqlRemOtp = $conn->query("UPDATE Resident_User SET OTP = NULL WHERE Email = '$email'");
                                        $this->returnResponse(200, "Password Changed.");
                                    }
                                    else
                                    {
                                        $this->returnResponse(200, "Password not Changed.");
                                    }
                                }
                                elseif($password != $confirmPassword)
                                {
                                    $this->throwError(200, "passwords don't match.");
                                }
                            }    
                        }
                        else
                        {
                            $this->throwError(200, "Wronge verification Number.");
                        }
                    }
                    else
                    {
                        $this->throwError(200, "OTP not found");
                    }
                }
                else
                {
                    $this->throwError(200, "Please wait till you recieve email with verification code.");
                }
            }
        }    
    }
}        

?>
