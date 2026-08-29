{{-- resources/views/pdf/resume.blade.php --}}

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $candidate ?? 'Resume' }}</title>

    <style>
        @page {
            margin: 28px 38px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
            line-height: 1.55;
            color: #2f3542;
            background: #ffffff;
        }

        .header {
            border-bottom: 3px solid #1f3c5b;
            padding-bottom: 16px;
            margin-bottom: 18px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .photo-cell {
            width: 105px;
        }

        .photo {
            width: 85px;
            height: 100px;
            object-fit: cover;
            border: 1px solid #d5d8dc;
            padding: 2px;
        }

        .name {
            margin: 0;
            padding: 0;
            font-size: 25px;
            font-weight: bold;
            color: #1f3c5b;
            text-transform: uppercase;
        }

        .designation {
            margin-top: 3px;
            font-size: 13px;
            color: #566573;
        }

        .contact-info {
            margin-top: 9px;
            font-size: 9.5px;
            color: #454545;
            line-height: 1.7;
        }

        .section {
            margin-bottom: 19px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1f3c5b;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 4px;
            margin-bottom: 10px;
        }

        .summary {
            text-align: justify;
        }

        .item {
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
        }

        .item-table td {
            vertical-align: top;
        }

        .item-title {
            font-size: 11.5px;
            font-weight: bold;
            color: #212f3d;
        }

        .company {
            font-size: 10px;
            font-weight: bold;
            color: #566573;
            margin-top: 2px;
        }

        .date {
            width: 145px;
            text-align: right;
            white-space: nowrap;
            font-size: 9px;
            color: #707b7c;
        }

        .description {
            margin-top: 6px;
            text-align: justify;
        }

        ul {
            margin-top: 6px;
            margin-bottom: 0;
            padding-left: 17px;
        }

        li {
            margin-bottom: 4px;
        }

        .education-extra {
            margin-top: 3px;
            font-size: 9.5px;
        }

        .personal-table {
            width: 100%;
            border-collapse: collapse;
        }

        .personal-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .personal-label {
            width: 150px;
            font-weight: bold;
            color: #34495e;
        }

        .declaration {
            text-align: justify;
        }

        .signature-table {
            margin-top: 30px;
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table td {
            vertical-align: bottom;
        }

        .signature {
            width: 220px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #555;
            padding-top: 5px;
            margin-top: 25px;
        }

        .footer {
            position: fixed;
            bottom: -15px;
            left: 0;
            right: 0;
            text-align: center;
            color: #999;
            font-size: 7.5px;
        }
    </style>
</head>

<body>

    {{-- ================= HEADER ================= --}}

    <div class="header">

        <table class="header-table">
            <tr>
                @php
                    $photoPath = public_path('storage/candidate/' . $photo);
                @endphp
                @if (!empty($photo))
                    <td class="photo-cell">

                        <img src="{{ public_path('storage/applicant/' . $photo) }}" class="photo" alt="Candidate Photo">

                    </td>
                @endif


                <td>

                    <h1 class="name">
                        {{ $candidate ?? '' }}
                    </h1>





                    <div class="contact-info">

                        @if (!empty($email))
                            <strong>Email:</strong>
                            {{ $email }}
                        @endif


                        @if (!empty($phone))

                            @if (!empty($email))
                                &nbsp;&nbsp; | &nbsp;&nbsp;
                            @endif

                            <strong>Phone:</strong>
                            {{ $phone }}

                        @endif



                        @if (!empty($linkedin))
                            <br>

                            <strong>LinkedIn:</strong>
                            {{ $linkedin }}
                        @endif

                    </div>

                </td>

            </tr>
        </table>

    </div>



    {{-- ================= PROFESSIONAL SUMMARY ================= --}}




    {{-- ================= PROFESSIONAL EXPERIENCE ================= --}}

    @if (!empty($experiences) && count($experiences) > 0)

        <div class="section">

            <div class="section-title">
                Professional Experience
            </div>


            @foreach ($experiences as $experience)
                <div class="item">

                    <table class="item-table">

                        <tr>

                            <td>

                                <div class="item-title">
                                    {{ $experience['designation'] ?? '' }}
                                </div>


                                <div class="company">

                                    {{ $experience['company'] ?? '' }}



                                </div>

                            </td>


                            <td class="date">

                                @if (!empty($experience['start_date']))
                                    {{ $experience['start_date'] }}
                                @endif


                                @if (!empty($experience['start_date']))
                                    -
                                @endif


                                @if (!empty($experience['end_date']))
                                    {{ $experience['end_date'] }}
                                @else
                                    Present
                                @endif

                            </td>

                        </tr>

                    </table>



                    {{-- Responsibilities --}}



                </div>
            @endforeach

        </div>

    @endif



    {{-- ================= EDUCATION ================= --}}

    @if (!empty($educations) && count($educations) > 0)

        <div class="section">

            <div class="section-title">
                Education
            </div>


            @foreach ($educations as $education)
                <div class="item">

                    <table class="item-table">

                        <tr>

                            <td>

                                <div class="item-title">
                                    {{ $education['degree_name'] ?? '' }}
                                </div>


                                <div class="company">
                                    {{ $education['institute'] ?? '' }}
                                </div>





                                @if (!empty($education['grade']))
                                    <div class="education-extra">

                                        <strong>Percentage:</strong>

                                        {{ $education['grade'] }} @if ($education['institute_type'] != 'university')
                                            %
                                        @else
                                            CGPA
                                        @endif

                                    </div>
                                @endif

                            </td>


                            <td class="date">

                                @if (!empty($education['graduate_start_year']))
                                    {{ $education['graduate_start_year'] }}
                                @endif


                                @if (!empty($education['graduate_start_year']) && !empty($education['graduate_end_year']))
                                    -
                                @endif


                                @if (!empty($education['graduate_end_year']))
                                    {{ $education['graduate_end_year'] }}
                                @endif

                            </td>

                        </tr>

                    </table>

                </div>
            @endforeach

        </div>

    @endif



    {{-- ================= PERSONAL INFORMATION ================= --}}

    @if (!empty($personal_details))

        <div class="section">

            <div class="section-title">
                Personal Information
            </div>


            <table class="personal-table">

                @if (!empty($personal_details['father_name']))
                    <tr>
                        <td class="personal-label">
                            Father's Name
                        </td>

                        <td>
                            {{ $personal_details['father_name'] }}
                        </td>
                    </tr>
                @endif


                @if (!empty($personal_details['dob']))
                    <tr>
                        <td class="personal-label">
                            Date of Birth
                        </td>

                        <td>
                            {{ $personal_details['dob'] }}
                        </td>
                    </tr>
                @endif


                @if (!empty($personal_details['gender']))
                    <tr>
                        <td class="personal-label">
                            Gender
                        </td>

                        <td>
                            {{ ucfirst($personal_details['gender']) }}
                        </td>
                    </tr>
                @endif


                @if (!empty($personal_details['cnic']))
                    <tr>
                        <td class="personal-label">
                            CNIC
                        </td>

                        <td>
                            {{ $personal_details['cnic'] }}
                        </td>
                    </tr>
                @endif




                {{--
                @if (!empty($personal_details['marital_status']))
                    <tr>
                        <td class="personal-label">
                            Marital Status
                        </td>

                        <td>
                            {{ ucfirst($personal_details['marital_status']) }}
                        </td>
                    </tr>
                @endif

--}}
                @if (!empty($personal_details['address']))
                    <tr>
                        <td class="personal-label">
                            Address
                        </td>

                        <td>
                            {{ $personal_details['address'] }}
                        </td>
                    </tr>
                @endif

            </table>

        </div>

    @endif



    {{-- ================= LANGUAGES ================= --}}




    {{-- ================= DECLARATION ================= --}}




    {{-- ================= SIGNATURE ================= --}}





    <div class="footer">
        {{ $candidate ?? 'Candidate' }} — Curriculum Vitae
    </div>

</body>

</html>
