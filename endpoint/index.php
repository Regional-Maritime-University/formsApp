<?php
session_start();
/*
* Designed and programmed by
* @Author: Francis A. Anlimah
*/

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require "../bootstrap.php";

use Src\Controller\ExposeDataController;

$expose = new ExposeDataController();

$data = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if ($_GET["url"] == "verifyStepFinal") {
        $arr = array();
        array_push($arr, $_SESSION["step1"], $_SESSION["step2"], $_SESSION["step4"], $_SESSION["step6"], $_SESSION["step7"]);
        echo json_encode($arr);
        //verify all sessions
        //save all user data
        //echo success message
    }
}

// All POST request will be sent here
elseif ($_SERVER['REQUEST_METHOD'] == "POST") {

    // verify user email address
    if ($_GET["url"] == "verifyUserEmailAddress") {

        if (!isset($_POST["email_address"]) || empty($_POST["email_address"]))
            die(json_encode(array("success" => false, "message" => "Email address is required!")));

        $email_address = $expose->validateEmail($_POST["email_address"]);
        $response = $expose->sendEmailVerificationCode($email_address);
        if (!$response["success"]) die(json_encode($response));

        $_SESSION["verification"]["type"] = "email";
        $_SESSION["verification"]["data"] = array("email_address" => $email_address);
        $_SESSION["verification"]['email_code'] = $response["otp_code"];
        $_SESSION["verification"]['sentStatus'] = true;

        die(json_encode(array("success" => true, "message" => "Code successfully sent to your email!")));
    }

    // verify user phone number
    elseif ($_GET["url"] == "verifyUserPhoneNumber") {
        if (!isset($_POST["country_code"]) || empty($_POST["country_code"]))
            die(json_encode(array("success" => false, "message" => "Country code is required!")));
        if (!isset($_POST["phone_number"]) || empty($_POST["phone_number"]))
            die(json_encode(array("success" => false, "message" => "Phone number is required!")));

        $country = $expose->validateCountryCode($_POST["country_code"]);
        $phone_number = $expose->validatePhone($_POST["phone_number"]);

        $charPos = strpos($country, ")");
        $country_name = substr($country, ($charPos + 2));
        $country_code = substr($country, 1, ($charPos - 1));
        $to = $country_code . $phone_number;
        $response = $expose->sendOTP($to);
        //die(json_encode(array("success" => true, "message" => $response)));

        if (!isset($response["otp_code"])) die(json_encode(array("success" => false, "message" => "Failed to send code to your phone number!")));

        $_SESSION["verification"]["type"] = "sms";
        $_SESSION["verification"]["data"] = array(
            "country_name" => $country_name,
            "country_code" => $country_code,
            "phone_number" => $phone_number,
        );
        $_SESSION["verification"]['sms_code'] = $response["otp_code"];
        $_SESSION["verification"]['sentStatus'] = true;

        die(json_encode(array("success" => true, "message" => "Code successfully sent to your phone number!")));
    }
    // verify step 5
    elseif ($_GET["url"] == "verifyCode") {
        if (!isset($_POST["code"]) || empty($_POST["code"]))
            die(json_encode(array("success" => false, "message" => "Verification code required!")));

        $code = $expose->validatePhone($_POST["code"]);
        switch ($_SESSION["verification"]["type"]) {
            case 'sms':
                if ($code == $_SESSION["verification"]['sms_code']) {
                    $_SESSION["verification"]["vStatus"] = "success";
                    $data["success"] = true;
                    $data["vData"] = "(" . $_SESSION["verification"]["data"]["country_code"] . ") " . $_SESSION["verification"]["data"]["phone_number"];
                } else {
                    $_SESSION["verification"]["vStatus"] = "failed";
                    $data["success"] = false;
                    $data["message"] = "OTP code provided is incorrect!";
                }
                break;

            case 'email':
                if ($code == $_SESSION["verification"]['email_code']) {
                    $_SESSION["verification"]["vStatus"] = "success";
                    $data["success"] = true;
                    $data["vData"] = $_SESSION["verification"]["data"]["email_address"];
                } else {
                    $_SESSION["verification"]["vStatus"] = "failed";
                    $data["success"] = false;
                    $data["message"] = "OTP code provided is incorrect!";
                }
                break;
        }
        die(json_encode($data));
    }

    //
    elseif ($_GET["url"] == "formInfo") {
        if (!isset($_POST["form_id"]) || empty($_POST["form_id"])) {
            die(json_encode(array("success" => false, "message" => "Error: Form has not been set properly in database!")));
        }

        $form_id = $expose->validateInput($_POST["form_id"]);
        $result = $expose->getFormPriceA($form_id);

        if (empty($result)) die(json_encode(array("success" => false, "message" => "Forms' price has not set in the database!")));
        die(json_encode(array("success" => true, "message" => $result)));
    }

    // verify step 6
    elseif ($_GET["url"] == "purchaseForm") {
        if (!isset($_SESSION["_purchaseToken"]) || empty($_SESSION["_purchaseToken"]) || !isset($_POST["_vPToken"]) || empty($_POST["_vPToken"]) || $_POST["_vPToken"] != $_SESSION["_purchaseToken"])
            die(json_encode(array("success" => false, "message" => "Invalid request! Failed to process your request.")));

        if (!isset($_POST["first-name"]) || empty($_POST["first-name"]))
            die(json_encode(array("success" => false, "message" => "First Name required!")));
        if (!isset($_POST["last-name"]) || empty($_POST["last-name"]))
            die(json_encode(array("success" => false, "message" => "Last Name required!")));
        if (!isset($_POST["available-forms"]) || empty($_POST["available-forms"]))
            die(json_encode(array("success" => false, "message" => "Form type is required!")));
        if (!isset($_POST["payment-method"]) || empty($_POST["payment-method"]))
            die(json_encode(array("success" => false, "message" => "Mode of payment required!")));
        if (!isset($_POST["form-price"]) || empty($_POST["form-price"]))
            die(json_encode(array("success" => false, "message" => "Choose a form type!")));
        if (!isset($_POST["email-address"]) || empty($_POST["email-address"]))
            die(json_encode(array("success" => false, "message" => "Email address is required!")));
        if (!isset($_POST["country-code"]) || empty($_POST["country-code"]))
            die(json_encode(array("success" => false, "message" => "Country code is required!")));
        if (!isset($_POST["phone-number"]) || empty($_POST["phone-number"]))
            die(json_encode(array("success" => false, "message" => "Phone number is required!")));

        $country = $expose->validateCountryCode($_POST["country-code"]);
        $phone_number = $expose->validatePhone($_POST["phone-number"]);

        $charPos = strpos($country, ")");
        $country_name = substr($country, ($charPos + 2));
        $country_code = substr($country, 1, ($charPos - 1));

        if (!isset($_SESSION["verification"]["vStatus"]) && $_SESSION["verification"]["vStatus"] != "success")
            die(json_encode(array("success" => false, "message" => "Please choose a means for verification before you can continue!")));

        $_SESSION["customerData"]["first_name"]     = $expose->validateInput($_POST["first-name"]);
        $_SESSION["customerData"]["last_name"]      = $expose->validateInput($_POST["last-name"]);
        $_SESSION["customerData"]["form_id"]        = $expose->validatePhone($_POST["available-forms"]);
        $_SESSION["customerData"]["email_address"]  = $expose->validateEmail($_POST["email-address"]);
        $_SESSION["customerData"]["country_name"]   = $country_name;
        $_SESSION["customerData"]["country_code"]   = $country_code;
        $_SESSION["customerData"]["phone_number"]   = $phone_number;
        $_SESSION["customerData"]["amount"]         = $_POST["form-price"];
        $_SESSION["customerData"]["pay_method"]     = $expose->validateText($_POST["payment-method"]);
        $_SESSION["customerData"]["vendor_id"]      = $_SESSION["vendor_id"];
        $_SESSION["customerData"]["admin_period"]   = $expose->getCurrentAdmissionPeriodID();

        $data = $expose->callOrchardGateway($_SESSION["customerData"]);

        /*session_unset();
        session_destroy();
        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }*/

        die(json_encode($data));
    }

    // Resend verification code
    elseif ($_GET["url"] == "resend-code") {
        if (!isset($_SESSION["verification"])) die(json_encode(array("success" => false, "message" => "Process could not complete!")));

        switch ($_SESSION["verification"]["type"]) {
            case 'sms':
                $to = $_SESSION["verification"]["data"]["country_code"] . $_SESSION["verification"]["data"]["phone_number"];
                $response = $expose->sendOTP($to);
                die(json_encode($response));
                if (!isset($response["otp_code"])) {
                    $_SESSION["verification"]['sms_code'] = "";
                    $_SESSION["verification"]['sentStatus'] = false;
                    $data["success"] = false;
                    $data["message"] = "Failed to send code to phone number!";
                } else {
                    $_SESSION["verification"]['sms_code'] = $response["otp_code"];
                    $_SESSION["verification"]['sentStatus'] = true;
                    $data["success"] = true;
                    $data["message"] = "Successfully sent code to phone number!";
                }
                break;

            case 'email':
                $response = $expose->sendEmailVerificationCode($_SESSION["verification"]["data"]["email_address"]);

                if (!$response["success"]) {
                    $_SESSION["verification"]['email_code'] = "";
                    $_SESSION["verification"]['sentStatus'] = false;
                    $data["success"] = false;
                    $data["message"] = $response["message"];
                } else {
                    $_SESSION["verification"]['email_code'] = $response["otp_code"];
                    $_SESSION["verification"]['sentStatus'] = true;
                    $data["success"] = true;
                    $data["message"] = "Successfully sent code to email!";
                }
                break;
        }
        die(json_encode($data));
    }

    //Online Payment confirmation
    elseif ($_GET["url"] == "confirm") {
        if (isset($_POST["status"]) && !empty($_POST["status"]) && isset($_POST["exttrid"]) && !empty($_POST["exttrid"])) {
            $status = $expose->validateInput($_POST["status"]);
            $transaction_id = $expose->validatePhone($_POST["exttrid"]);
            die(json_encode($expose->verifyPurchaseStatus($transaction_id)));
        } else {
            $data["success"] = false;
            $data["message"] = "Invalid request!";
        }
        die(json_encode($data));
    }
} else {
    http_response_code(405);
}
