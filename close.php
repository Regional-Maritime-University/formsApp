<?php
    require_once("bootstrap.com");
    use Src\Controller\ExposeDataController;
    $expose = new ExposeDataController();
    
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/main.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&family=Roboto+Mono:wght@700&family=Ubuntu:wght@400;700&display=swap" rel="stylesheet">
    <title>Admission Closed</title>
    <style>
        .flex-container>div {
            height: 100px !important;
            width: 100% !important;
        }
    </style>
</head>

<body class="fluid-container">

    <div id="wrapper">

        <?php require_once("./inc/page-nav-text-logo.php"); ?>

        <main class="container flex-container" style="height: 450px !important">
            <div class="flex-column align-items-center">
                <h1 style="text-align: center;"><span>January</span> admission has ended.</h1>
                <p style="font-size: 20px;">For more information and enquiries, <a href="https://rmu.edu.gh/contact/"><b>contact us</b></a>.</p>
            </div>
        </main>
    </div>
</body>

</html>