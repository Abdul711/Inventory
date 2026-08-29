<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Experience Letter</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
            color: #222;
            font-size: 14px;
            line-height: 1.7;
        }

        .page {
            padding: 45px 55px;
        }

        .border {
            border: 3px solid #1f3b5b;
            padding: 6px;
        }

        .inner-border {
            border: 1px solid #b79a58;
            padding: 40px 45px 50px;
            min-height: 650px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 30px;
            color: #1f3b5b;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .header-line {
            width: 100px;
            height: 2px;
            background: #b79a58;
            margin: 12px auto;
        }

        .photo-wrapper {
            text-align: center;
            margin-bottom: 25px;
        }

        .photo {
            width: 110px;
            height: 130px;
            border: 2px solid #1f3b5b;
            padding: 3px;
        }

        .subject {
            font-weight: bold;
            margin-bottom: 25px;
        }

        .content {
            text-align: justify;
        }

        .highlight {
            font-weight: bold;
            color: #1f3b5b;
        }

        .details {
            margin: 25px 0;
            width: 100%;
            border-collapse: collapse;
        }

        .details td {
            padding: 8px 5px;
            border-bottom: 1px solid #ddd;
        }

        .details td:first-child {
            width: 35%;
            font-weight: bold;
            color: #1f3b5b;
        }

        .footer {
            margin-top: 55px;
        }

        .signature {
            margin-top: 60px;
            width: 220px;
            border-top: 1px solid #333;
            padding-top: 8px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="page">
        <div class="border">
            <div class="inner-border">

                <div class="header">
                    <h1>Experience Letter</h1>
                    <div class="header-line"></div>
                </div>

                {{-- Applicant Photo --}}
                @if (!empty($applicannt_photo))
                    @php
                        $photoPath = public_path('storage/candidate/' . $applicannt_photo);
                    @endphp

                    @if (file_exists($photoPath))
                        <div class="photo-wrapper">
                            <img src="{{ $photoPath }}" class="photo" alt="Applicant Photo">
                        </div>
                    @endif
                @endif

                <div class="subject">
                    To Whom It May Concern
                </div>

                <div class="content">
                    @php
                        $photoPath = public_path('storage/applicant/' . $photo);
                    @endphp
                    @if (!empty($photo))
                        <img src="{{ public_path('storage/applicant/' . $photo) }}" class="photo"
                            alt="Candidate Photo">
                    @endif
                    <p>
                        This is to certify that
                        <span class="highlight">
                            {{ $candidate }}
                        </span>


                        @if ($gender == 'male')
                            S/O
                        @else
                            D/O
                        @endif

                        <span class="highlight">
                            {{ $father_name }}
                        </span>
                        has worked with
                        <span class="highlight">
                            {{ $company_name }}
                        </span>
                        at the position of
                        <span class="highlight">
                            {{ $designation }}
                        </span>
                        <br>
                        from
                        <span class="highlight">
                            {{ date('d-F-Y', strtotime($from)) }}
                        </span>
                        to
                        <span class="highlight">
                            {{ date('d-F-Y', strtotime($to)) }}
                        </span>.
                    </p>

                    <p>
                        During this period,
                        {{ $candidate }}
                        gained valuable professional experience and performed
                        assigned responsibilities with dedication and professionalism.
                    </p>

                    <table class="details">


                        <tr>
                            <td>Date of Birth</td>
                            <td>{{ $date_of_birth }}</td>
                        </tr>

                        <tr>
                            <td>CNIC</td>
                            <td>{{ $cnic }}</td>
                        </tr>



                        <tr>
                            <td>Total Experience</td>
                            <td>{{ $experience }}</td>
                        </tr>
                    </table>

                    <p>
                        We appreciate the services rendered by
                        <span class="highlight">
                            {{ $candidate }}
                        </span>
                        during the period of association with
                        <span class="highlight">
                            {{ $company_name }}
                        </span>.
                    </p>

                    <p>
                        We wish
                        @if ($gender == 'male')
                            him
                        @else
                            her
                        @endif
                        success in future professional endeavors.
                    </p>

                </div>



            </div>
        </div>
    </div>

</body>

</html>
