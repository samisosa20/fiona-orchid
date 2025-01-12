<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>email</title>

    <style>
        @import url("https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap");

        body {
            margin: 0 auto !important;
            background: #201d2c !important;
        }

        .body {
            background: #201d2c !important;
            font-family: "Nunito Sans", sans-serif;
            position: relative;
            padding: 24px 0px;
            max-width: 425px;
            background-repeat: no-repeat;
            background-size: 100% 100%;
            color: white;
            margin: auto !important;
        }

        .w-full {
            width: 100%;
        }

        .text-center {
            text-align: center;
        }

        .title {
            font-size: 22px;
            font-weight: 700;
            line-height: 22px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: white
        }

        .text-montserrat {
            font-size: 14px;
            font-weight: 500;
            line-height: 20px;
            letter-spacing: -0.006em;
        }

        .text-avenir {
            font-size: 16px;
            font-weight: 600;
            line-height: 22px;
            letter-spacing: -0.0011em;
        }

        .text-legal {
            font-family: Montserrat;
            font-size: 12px;
            font-weight: 500;
            line-height: 17px;
            letter-spacing: 0em;
            text-align: center;
            color: white;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 22px;
            background: #facc15;
            font-size: 14px;
            font-weight: 500;
            line-height: 21px;
            letter-spacing: -0.0009em;
            text-align: center;
            text-decoration: none;
            color: white;
        }

        .mb-6 {
            margin-bottom: 1.5rem;
        }

        .mt-8 {
            margin-top: 2rem;
        }

        .mb-10 {
            margin-bottom: 2.5rem;
        }

        .mt-4 {
            margin-top: 1rem;
        }

        .mt-16 {
            margin-top: 4rem;
        }

        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }

        .table-tyc {
            width: 100%;
        }

        .table-tyc td {
            text-align: center;
        }

        .pt-4 {
            padding-top: 16px;
        }
        .table-tyc a {
            font-size: 12px;
            font-weight: 400;
            line-height: 17px;
            letter-spacing: 0em;
            color: #B0B1B2;
        }

        @media (min-width: 425px) {
            body {
                margin: 16px auto;
            }
        }
    </style>
</head>

<body>
    <div class="body">
        <div class="text-center pt-4">
            <img src="https://api.finanzaspersonalesemma.com/img/logo.png" alt="Logo Emma" class="mx-auto" width="200"
                height="57" />
        </div>
        <div class="text-center mb-10 mt-8">
            <img src="https://api.finanzaspersonalesemma.com/img/emma-ref-balance.png" alt="Balance Emma" width="400"
                height="145" class="mx-auto" />
        </div>
        <h1 class="text-center title mb-6">
            Hola {{ $user->name }}!
        </h1>
        <p class="text-center text-montserrat mb-6">
            Asi es como va tus finanzas en <span style="color: #facc15;">Emma</span>.
        </p>
        <p class="text-center text-montserrat mb-6">
            Asi van tus finanzas en lo que va del mes
        </p>
        <h3 class="text-center text-avenir mb-6">
            Ingresos: <span style="color: #22c55e;">{{ number_format($balance->income, 2, ',', '.') }}</span>
        </h3>
        <h3 class="text-center text-avenir mb-6">
            Egresos: <span style="color: #ef4444;">{{ number_format(abs($balance->expensive), 2, ',', '.') }}</span>
        </h3>
        <h3 class="text-center text-avenir mb-6">
            Utilidad: <span
                style="color: {{ $balance->income + $balance->expensive < 0 ? '#ef4444' : '#22c55e' }};">{{ number_format($balance->income + $balance->expensive, 2, ',', '.') }}</span>
        </h3>
        <center>
            <p class="text-center text-montserrat mb-6">
                Estamos a pocos dias de que se acabe el mes,<br /> si te falta por ingresa informacion, que esperas para
                reportarla
            </p>
            <table>
                <tbody>
                    <tr>
                        <td><a href="https://finanzaspersonalesemma.com/login" style="text-decoration: none;">
                                <div class="text-center btn mb-6">Ir a la App</div>
                            </a></td>
                    </tr>
                </tbody>
            </table>
        </center>
        <div class="mt-16" style="width: 100%; padding: 4px 0px; background: #363635;">
            <p class="text-legal mx-auto" style="width: 90%;">© Copyright 2024</p>
        </div>
    </div>
</body>

</html>
