<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <style>
.forget-boxwp {
    text-align: center;
    margin: 0 30%;
    padding: 30px 20px;
    background: #f0f0f0;
}

.forget-boxwp button {
    padding: 8px 16px;
    background: red;
    color: #fff;
    border: none;
    border-radius: 3px;
}

.imglogo-bg{
    padding-bottom: 20px; max-width: 300px    
}

    @media screen and (max-width: 600px) {
        .forget-boxwp {
        margin: 10% 0% 0;
    }
}

    </style>
</head>
<body>
    <section class="forget-passwordwp" style="padding-top: 40px; text-align: center;">
        <div class="container">
            <div class="forget-boxwp">
                <img class="imglogo-bg" src="https://mighzalalarab.com/wp-content/uploads/2022/06/Master-Logo-2048x492.png" alt="#" style="">
                <h2>Forget Password</h2>
                <p>	
                    Hi {{ $testMailData['name'] }},
                    <br>
                    We got a request to reset your Instagram password.</p>
                <a href="{{ $testMailData['link'] }}">
                    <button>
                        Reset Password
                    </button>
                </a>
                <p>If you ignore this message, your password will not be changed. </p>
            </div>
        </div>
    </section>
</body>
</html>