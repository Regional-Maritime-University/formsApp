<?php

namespace Src\Controller;

use Src\System\DatabaseMethods;
use Src\Controller\PaymentController;
use Src\Gateway\CurlGatewayAccess;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ExposeDataController
{
    private $dm;

    public function __construct()
    {
        $this->dm = new DatabaseMethods();
    }

    public function genCode($length = 6)
    {
        $digits = $length;
        $first = pow(10, $digits - 1);
        $second = pow(10, $digits) - 1;
        return rand($first, $second);
    }

    public function validateEmail($input)
    {
        if (empty($input)) die(json_encode(array("success" => false, "message" => "Input required!")));
        $user_email = htmlentities(htmlspecialchars($input));
        $sanitized_email = filter_var($user_email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($sanitized_email, FILTER_VALIDATE_EMAIL))
            die(json_encode(array("success" => false, "message" => "Invalid email address!" . $sanitized_email)));
        return $user_email;
    }

    public function validateInput($input)
    {
        if (empty($input)) die(json_encode(array("success" => false, "message" => "Input required!")));
        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[A-Za-z0-9]/', $user_input);
        if ($validated_input) return $user_input;
        die(json_encode(array("success" => false, "message" => "Invalid input!")));
    }

    public function validateCountryCode($input)
    {
        if (empty($input)) die(json_encode(array("success" => false, "message" => "Input required!")));
        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[A-Za-z0-9()+]/', $user_input);
        if ($validated_input) return $user_input;
        die(json_encode(array("success" => false, "message" => "Invalid input!")));
    }

    public function validatePhone($input)
    {
        if (empty($input)) die(json_encode(array("success" => false, "message" => "Input required!")));
        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[0-9]/', $user_input);
        if ($validated_input) return $user_input;
        die(json_encode(array("success" => false, "message" => "Invalid input!")));
    }

    public function validateText($input)
    {
        if (empty($input)) die(json_encode(array("success" => false, "message" => "Input required!")));
        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[A-Za-z]/', $user_input);
        if ($validated_input) return $user_input;
        die(json_encode(array("success" => false, "message" => "Invalid input!")));
    }

    public function getCurrentAdmissionPeriodID()
    {
        //return $this->dm->getData("SELECT * FROM `admission_period` WHERE `active` = 1 OR deadline <> NOW()");
        return $this->dm->getID("SELECT `id` FROM `admission_period` WHERE `active` = 1");
    }

    public function getFormPriceA(int $form_id)
    {
        return $this->dm->getData("SELECT * FROM `forms` WHERE `id` = :fi", array(":fi" => $form_id));
    }

    public function getAdminYearCode()
    {
        $sql = "SELECT EXTRACT(YEAR FROM (SELECT `start_date` FROM admission_period WHERE active = 1)) AS 'year'";
        $year = (string) $this->dm->getData($sql)[0]['year'];
        return (int) substr($year, 2, 2);
    }

    public function getAvailableForms()
    {
        return $this->dm->getData("SELECT * FROM `forms`");
    }

    public function sendEmail($recipient_email, $subject, $message)
    {
        //PHPMailer Object
        $mail = new PHPMailer(true); //Argument true in constructor enables exceptions
        //From email address and name
        $mail->From = "rmuicton@rmuictonline.com";
        $mail->FromName = "RMU Forms Online";

        //To address and name
        $mail->addAddress($recipient_email);
        //Send HTML or Plain Text email
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        try {
            if ($mail->send()) return array("success" => true);
        } catch (Exception $e) {
            return array("success" => false, "message" => "Mailer Error: " . $mail->ErrorInfo);
        }
    }

    public function sendHubtelSMS($url, $payload)
    {
        $client = getenv('HUBTEL_CLIENT');
        $secret = getenv('HUBTEL_SECRET');
        $secret_key = base64_encode($client . ":" . $secret);
        $httpHeader = array("Authorization: Basic " . $secret_key, "Content-Type: application/json");
        $gateAccess = new CurlGatewayAccess($url, $httpHeader, $payload);
        return $gateAccess->initiateProcess();
    }

    public function sendSMS($to, $message)
    {
        $url = "https://sms.hubtel.com/v1/messages/send";
        $payload = json_encode(array("From" => "RMU", "To" => $to, "Content" => $message));
        return $this->sendHubtelSMS($url, $payload);
    }

    public function sendOTP($to)
    {
        $otp_code = $this->genCode(6);
        $message = 'Your OTP verification code: ' . $otp_code;
        $response = $this->sendSMS($to, $message);
        $this->requestLogger($response);
        $res = json_decode($response, true);
        if (!$res["status"]) $res["otp_code"] = $otp_code;
        return $res;
    }

    public function sendEmailVerificationCode($email)
    {
        $v_code = $this->genCode(6);
        $subject = 'RMU Forms Online Verification Code';
        $message = "Hi,";
        $message .= "<p>This is your verification code <b style='font-size: 20px'>" . $v_code . ".</b></p>";
        $message .= "<p>Codes expires after 30 minutes.</p>";
        $message .= "<p>Thank you.</p>";
        $response = $this->sendEmail($email, $subject, $message);
        if (!$response["success"]) return $response;
        $response["otp_code"] = $v_code;
        return $response;
    }

    /**
     * @param int transaction_id //transaction_id
     */
    public function callOrchardGateway($data)
    {
        $payConfirm = new PaymentController();
        return $payConfirm->orchardPaymentController($data);
    }

    /**
     * @param int transaction_id //transaction_id
     */
    public function confirmPurchase(int $transaction_id)
    {
        $payConfirm = new PaymentController();
        return $payConfirm->processTransaction($transaction_id);
    }

    public function processVendorPay($data)
    {
        $payConfirm = new PaymentController();
        return $payConfirm->vendorPaymentProcess($data);
    }

    public function verifyPurchaseStatus($data)
    {
        $payConfirm = new PaymentController();
        return $payConfirm->verifyPurchaseStatus($data);
    }

    public function requestLogger($request)
    {
        $query = "INSERT INTO `ussd_request_logs` (`request`) VALUES(:nc)";
        $params = array(":nc" => $request);
        $this->dm->inputData($query, $params);
    }
}
