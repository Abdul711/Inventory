<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #eee;
            padding: 40px;
        }

        .cnic {
            width: 856px;
            height: 540px;
            background: #ffffff;
            border-radius: 18px;
            border: 3px solid #0a7d3b;
            overflow: hidden;
            position: relative;
        }

        .header {
            background: #0a7d3b;
            color: white;
            padding: 18px 25px;
            font-size: 30px;
            font-weight: bold;
        }

        .logo {
            position: absolute;
            top: 20px;
            right: 25px;
            width: 70px;
        }

        .photo {
            position: absolute;
            right: 35px;
            top: 120px;
            width: 180px;
            height: 220px;
            border: 2px solid #999;
            object-fit: cover;
        }

        .details {
            padding: 40px;
            margin-top: 30px;
            width: 600px;
        }

        .row {
            margin-bottom: 16px;
            font-size: 24px;
        }

        .label {
            display: inline-block;
            width: 220px;
            font-weight: bold;
        }

        .footer {
            position: absolute;
            bottom: 25px;
            left: 30px;
            font-size: 28px;
            font-weight: bold;
        }

        .qr {
            position: absolute;
            bottom: 20px;
            right: 35px;
            width: 120px;
        }
    </style>

</head>

<body>

    <div class="cnic">

        <div class="header">
            National Identity Card
        </div>



        <img class="photo" src="{{ public_path('storage/applicant/' . $applicannt_photo) }}">

        <div class="details">

            <div class="row">
                <span class="label">Name</span>
                {{ $name }}
            </div>

            <div class="row">
                <span class="label">Father Name</span>
                {{ $father_name }}
            </div>

            <div class="row">
                <span class="label">CNIC</span>
                {{ $cnic }}
            </div>

            <div class="row">
                <span class="label">Date of Birth</span>
                {{ $date_of_birth }}
            </div>

            <div class="row">
                <span class="label">Gender</span>
                {{ $gender }}
            </div>

            <div class="row">
                <span class="label">Address</span>
                {{ $address }}
            </div>
            @if (isset($expiry_date))
                <div class="row">
                    <span class="label">Expiry Date</span>
                    {{ $expiry_date }}
                </div>
            @endif
            @if (isset($issue_date))
                <div class="row">
                    <span class="label">Issue Date</span>
                    {{ $issue_date }}
                </div>
            @endif
        </div>





    </div>

</body>

</html>
