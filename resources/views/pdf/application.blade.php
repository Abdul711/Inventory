<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        Job Application #{{ $application->id }}
    </title>

    <style>
        @page {
            margin: 20px 24px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.5;
            color: #1f2937;
            background: #ffffff;
        }

        .page {
            width: 100%;
        }

        .header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .header td {
            border: none;
            vertical-align: top;
        }

        .document-title {
            font-size: 25px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 4px;
        }

        .document-subtitle {
            color: #6b7280;
            font-size: 10px;
            margin-bottom: 9px;
        }

        .application-badge {
            display: inline-block;
            padding: 5px 10px;
            background: #eef2ff;
            color: #3730a3;
            border-radius: 15px;
            font-size: 9px;
            font-weight: bold;
        }

        .qr-wrapper {
            text-align: right;
        }

        .qr-wrapper img {
            width: 85px;
            height: 85px;
        }

        .qr-caption {
            font-size: 7px;
            color: #6b7280;
            margin-top: 2px;
        }

        /* ================================
           Candidate Header
        ================================= */

        .hero {
            background: #111827;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 18px;
            color: #ffffff;
        }

        .hero-table {
            width: 100%;
            border-collapse: collapse;
        }

        .hero-table td {
            border: none;
            vertical-align: middle;
        }

        .photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid #ffffff;
        }

        .photo-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid #ffffff;
            background: #374151;
            color: #d1d5db;
            text-align: center;
            line-height: 74px;
            font-size: 8px;
        }

        .candidate-name {
            font-size: 20px;
            font-weight: bold;
            color: #ffffff;
            margin-bottom: 4px;
        }

        .candidate-contact {
            color: #d1d5db;
            font-size: 9px;
            margin-bottom: 2px;
        }

        .candidate-job-label {
            color: #9ca3af;
            font-size: 8px;
            margin-top: 7px;
        }

        .candidate-job {
            color: #ffffff;
            font-size: 13px;
            font-weight: bold;
        }

        .status {
            display: inline-block;
            background: #ffffff;
            color: #111827;
            padding: 6px 10px;
            border-radius: 14px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* ================================
           General Sections
        ================================= */

        .section {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #111827;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 5px;
            margin-bottom: 9px;
        }

        .position-card {
            border: 1px solid #e5e7eb;
            border-left: 5px solid #111827;
            background: #f9fafb;
            border-radius: 6px;
            padding: 11px;
        }

        .position-title {
            font-size: 15px;
            color: #111827;
            font-weight: bold;
        }

        .position-department {
            color: #6b7280;
            font-size: 9px;
            margin-top: 2px;
        }

        /* ================================
           Information Grid
        ================================= */

        .info-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px;
            margin-left: -5px;
            margin-right: -5px;
        }

        .info-table td {
            width: 50%;
            border: none;
            vertical-align: top;
        }

        .info-box {
            border: 1px solid #e5e7eb;
            background: #fafafa;
            border-radius: 5px;
            padding: 9px;
            min-height: 45px;
        }

        .label {
            font-size: 7px;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: .4px;
            margin-bottom: 3px;
        }

        .value {
            font-size: 10px;
            color: #111827;
            font-weight: bold;
            word-wrap: break-word;
        }

        /* ================================
           Education
        ================================= */

        .history-table {
            width: 100%;
            border-collapse: collapse;
        }

        .history-table th {
            background: #f3f4f6;
            color: #374151;
            padding: 7px;
            border: 1px solid #e5e7eb;
            font-size: 7px;
            text-transform: uppercase;
            text-align: left;
        }

        .history-table td {
            padding: 7px;
            border: 1px solid #e5e7eb;
            font-size: 8px;
            vertical-align: top;
        }

        .history-primary {
            font-weight: bold;
            color: #111827;
        }

        /* ================================
           Work Experience
        ================================= */

        .work-card {
            border: 1px solid #e5e7eb;
            border-left: 4px solid #111827;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }

        .work-header {
            width: 100%;
            border-collapse: collapse;
        }

        .work-header td {
            border: none;
            vertical-align: top;
        }

        .work-designation {
            font-size: 12px;
            color: #111827;
            font-weight: bold;
        }

        .work-company {
            color: #4b5563;
            font-size: 9px;
            margin-top: 2px;
        }

        .work-date {
            text-align: right;
            color: #6b7280;
            font-size: 8px;
        }

        .work-description {
            border-top: 1px solid #f3f4f6;
            margin-top: 7px;
            padding-top: 7px;
            color: #4b5563;
            font-size: 8px;
        }

        .work-meta {
            margin-top: 6px;
            color: #6b7280;
            font-size: 8px;
        }

        /* ================================
           Salary
        ================================= */

        .salary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px;
            margin-left: -5px;
        }

        .salary-table td {
            width: 33.333%;
            border: none;
            vertical-align: top;
        }

        .salary-card {
            padding: 9px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            border-radius: 5px;
        }

        .salary-value {
            color: #111827;
            font-size: 12px;
            font-weight: bold;
            margin-top: 3px;
        }

        .empty {
            background: #f9fafb;
            border: 1px dashed #d1d5db;
            padding: 11px;
            color: #6b7280;
            text-align: center;
            border-radius: 5px;
            font-size: 8px;
        }

        /* ================================
           Declaration
        ================================= */

        .declaration {
            padding: 11px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            border-radius: 5px;
            color: #4b5563;
            font-size: 8px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        .signature-table td {
            width: 50%;
            border: none;
            vertical-align: bottom;
        }

        .signature-line {
            border-top: 1px solid #9ca3af;
            padding-top: 4px;
            width: 180px;
            font-size: 8px;
            color: #6b7280;
        }

        .footer {
            margin-top: 18px;
            padding-top: 9px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 7px;
        }
    </style>
</head>

<body>

    <div class="page">

        {{-- =================================================
        HEADER
    ================================================== --}}

        <table class="header">

            <tr>

                <td style="width:75%;">

                    <div class="document-title">
                        Job Application
                    </div>

                    <div class="document-subtitle">
                        Recruitment Candidate Profile
                    </div>

                    <div class="application-badge">
                        Application #{{ $application->id }}
                    </div>

                </td>

                <td style="width:25%;" class="qr-wrapper">

                    @if (!empty($qrCode))
                        <img src="{{ $qrCode }}" alt="Application QR Code">

                        <div class="qr-caption">
                            Scan to verify application
                        </div>
                    @endif

                </td>

            </tr>

        </table>


        {{-- =================================================
        CANDIDATE HERO
    ================================================== --}}

        <div class="hero">

            <table class="hero-table">

                <tr>

                    <td style="width:17%;">

                        @if (!empty($photo))
                            <img src="{{ $photo }}" class="photo" alt="Applicant Photo">
                        @else
                            <div class="photo-placeholder">
                                NO PHOTO
                            </div>
                        @endif

                    </td>


                    <td style="width:58%; padding-left:12px;">

                        <div class="candidate-name">

                            {{ $application->applicant->full_name ?? '-' }}

                        </div>

                        <div class="candidate-contact">

                            {{ $application->applicant->email ?? '-' }}

                        </div>

                        <div class="candidate-contact">

                            {{ $application->applicant->phone ?? '-' }}

                        </div>

                        <div class="candidate-job-label">
                            POSITION APPLIED FOR
                        </div>

                        <div class="candidate-job">

                            {{ $application->jobPosting->designation->name ?? '-' }}

                        </div>

                    </td>


                    <td style="width:25%; text-align:right;">

                        <span class="status">

                            {{ \Illuminate\Support\Str::headline($application->status ?? 'pending') }}

                        </span>

                    </td>

                </tr>

            </table>

        </div>


        {{-- =================================================
        POSITION INFORMATION
    ================================================== --}}

        <div class="section">

            <div class="section-title">
                Position Information
            </div>

            <div class="position-card">

                <div class="position-title">

                    {{ $application->jobPosting->designation->name ?? '-' }}

                </div>

                <div class="position-department">

                    Department:

                    {{ $application->jobPosting->department->name ?? '-' }}

                </div>

            </div>


            <table class="info-table" style="margin-top:8px;">

                <tr>

                    <td>

                        <div class="info-box">

                            <div class="label">
                                Job Mode
                            </div>

                            <div class="value">

                                {{ \Illuminate\Support\Str::headline($application->jobPosting->work_mode ?? '-') }}

                            </div>

                        </div>

                    </td>


                    <td>

                        <div class="info-box">

                            <div class="label">
                                Employment Type
                            </div>

                            <div class="value">

                                {{ \Illuminate\Support\Str::headline($application->jobPosting->employment_type ?? '-') }}

                            </div>

                        </div>

                    </td>

                </tr>

            </table>

        </div>


        {{-- =================================================
        PERSONAL INFORMATION
    ================================================== --}}

        <div class="section">

            <div class="section-title">
                Personal Information
            </div>

            <table class="info-table">


                {{-- ROW 1 --}}

                <tr>

                    <td>

                        <div class="info-box">

                            <div class="label">
                                Full Name
                            </div>

                            <div class="value">

                                {{ $application->applicant->full_name ?? '-' }}

                            </div>

                        </div>

                    </td>


                    <td>

                        <div class="info-box">

                            <div class="label">
                                Father Name
                            </div>

                            <div class="value">

                                {{ $application->applicant->father_name ?? '-' }}

                            </div>

                        </div>

                    </td>

                </tr>


                {{-- ROW 2 --}}

                <tr>

                    <td>

                        <div class="info-box">

                            <div class="label">
                                Email Address
                            </div>

                            <div class="value">

                                {{ $application->applicant->email ?? '-' }}

                            </div>

                        </div>

                    </td>


                    <td>

                        <div class="info-box">

                            <div class="label">
                                Phone Number
                            </div>

                            <div class="value">

                                {{ $application->applicant->phone ?? '-' }}

                            </div>

                        </div>

                    </td>

                </tr>


                {{-- ROW 3 --}}

                <tr>

                    <td>

                        <div class="info-box">

                            <div class="label">
                                CNIC
                            </div>

                            <div class="value">

                                {{ $application->applicant->cnic ?? '-' }}

                            </div>

                        </div>

                    </td>


                    <td>

                        <div class="info-box">

                            <div class="label">
                                Gender
                            </div>

                            <div class="value">

                                {{ \Illuminate\Support\Str::headline($application->applicant->gender ?? '-') }}

                            </div>

                        </div>

                    </td>

                </tr>


                {{-- DOB / AGE --}}
                <tr>

                    <td>

                        <div class="info-box">

                            <div class="label">
                                Marital Status
                            </div>

                            <div class="value">

                                {{ \Illuminate\Support\Str::headline($application->applicant->martial_status ?? '-') }}

                            </div>

                        </div>

                    </td>


                    <td>

                        <div class="info-box">

                            <div class="label">
                                LinkedIn
                            </div>

                            <div class="value">

                                @if (!empty($application->applicant->linkedin))
                                    {{ $application->applicant->linkedin }}
                                @else
                                    -
                                @endif

                            </div>

                        </div>

                    </td>

                </tr>

                <tr>

                    <td>

                        <div class="info-box">

                            <div class="label">
                                Date of Birth
                            </div>

                            <div class="value">

                                @if (!empty($application->applicant->date_of_birth))
                                    {{ \Carbon\Carbon::parse($application->applicant->date_of_birth)->format('d M Y') }}
                                @else
                                    -
                                @endif

                            </div>

                        </div>

                    </td>


                    <td>

                        <div class="info-box">

                            <div class="label">
                                Age
                            </div>

                            <div class="value">

                                @if (!empty($application->applicant->date_of_birth))
                                    {{ \Carbon\Carbon::parse($application->applicant->date_of_birth)->age }}
                                    Years
                                @else
                                    -
                                @endif

                            </div>

                        </div>

                    </td>

                </tr>


                {{-- ADDRESS --}}

                <tr>

                    <td colspan="2">

                        <div class="info-box">

                            <div class="label">
                                Address
                            </div>

                            <div class="value">

                                {{ $application->applicant->address ?? '-' }}

                            </div>

                        </div>

                    </td>

                </tr>

            </table>

        </div>


        {{-- =================================================
        EDUCATION HISTORY
    ================================================== --}}

        <div class="section">

            <div class="section-title">
                Education History
            </div>


            @if (isset($application->educations) && $application->educations->count() > 0)

                <table class="history-table">

                    <thead>

                        <tr>

                            <th>Qualification</th>

                            <th>Institute</th>

                            <th>Start</th>

                            <th>End</th>

                            <th>Result</th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach ($application->educations as $education)
                            <tr>

                                <td>

                                    <span class="history-primary">

                                        {{ $education->degree_name ?? ($education->qualification ?? ($education->education_level ?? '-')) }}

                                    </span>

                                </td>


                                <td>

                                    {{ $education->institute_name ?? ($education->institute ?? '-') }}

                                </td>


                                <td>

                                    {{ $education->graduate_start_year ?? ($education->start_date ?? '-') }}

                                </td>


                                <td>

                                    {{ $education->graduate_end_year ?? ($education->end_date ?? 'Present') }}

                                </td>


                                <td>

                                    {{ $education->grade ?? ($education->cgpa ?? ($education->percentage ?? '-')) }}

                                </td>

                            </tr>
                        @endforeach

                    </tbody>

                </table>
            @else
                <div class="empty">
                    No education history available.
                </div>

            @endif

        </div>


        {{-- =================================================
        WORK EXPERIENCE
    ================================================== --}}

        <div class="section">

            <div class="section-title">
                Work Experience
            </div>


            @if (isset($application->works) && $application->works->count() > 0)

                @foreach ($application->works as $work)
                    <div class="work-card">

                        <table class="work-header">

                            <tr>

                                <td style="width:70%;">

                                    <div class="work-designation">

                                        {{ $work->designation ?? ($work->job_title ?? '-') }}

                                    </div>

                                    <div class="work-company">

                                        {{ $work->company_name ?? ($work->company ?? '-') }}

                                    </div>

                                </td>


                                <td style="width:30%;">

                                    <div class="work-date">

                                        @if (!empty($work->start_date))
                                            {{ \Carbon\Carbon::parse($work->start_date)->format('M Y') }}
                                        @else
                                            -
                                        @endif


                                        &nbsp;-&nbsp;


                                        @if (!empty($work->is_current))
                                            Present
                                        @elseif(!empty($work->end_date))
                                            {{ \Carbon\Carbon::parse($work->end_date)->format('M Y') }}
                                        @else
                                            Present
                                        @endif

                                    </div>

                                </td>

                            </tr>

                        </table>


                        @if (!empty($work->job_description) || !empty($work->description))
                            <div class="work-description">

                                {{ $work->job_description ?? $work->description }}

                            </div>
                        @endif


                        @if (!empty($work->reason_for_leaving))
                            <div class="work-meta">

                                <strong>
                                    Reason for Leaving:
                                </strong>

                                {{ $work->reason_for_leaving }}

                            </div>
                        @endif

                    </div>
                @endforeach
            @else
                <div class="empty">
                    No work experience history available.
                </div>

            @endif

        </div>


        {{-- =================================================
        APPLICATION DETAILS
    ================================================== --}}

        <div class="section">

            <div class="section-title">
                Application Details
            </div>

            <table class="info-table">

                <tr>

                    <td>

                        <div class="info-box">

                            <div class="label">
                                Last Education
                            </div>

                            <div class="value">

                                {{ $application->last_education ?? '-' }}

                            </div>

                        </div>

                    </td>


                    <td>

                        <div class="info-box">

                            <div class="label">
                                Last Institute
                            </div>

                            <div class="value">

                                {{ $application->last_institute ?? '-' }}

                            </div>

                        </div>

                    </td>

                </tr>


                <tr>

                    <td>

                        <div class="info-box">

                            <div class="label">
                                Current Company
                            </div>

                            <div class="value">

                                {{ $application->current_company ?? '-' }}

                            </div>

                        </div>

                    </td>


                    <td>

                        <div class="info-box">

                            <div class="label">
                                Total Experience
                            </div>

                            <div class="value">

                                @php
                                    $months = (int) ($application->month_of_exprience ?? 0);
                                    $years = intdiv($months, 12);
                                    $remainingMonths = $months % 12;
                                @endphp

                                @if ($years > 0)
                                    {{ $years }}

                                    {{ $years === 1 ? 'Year' : 'Years' }}
                                @endif


                                @if ($remainingMonths > 0)
                                    {{ $remainingMonths }}

                                    {{ $remainingMonths === 1 ? 'Month' : 'Months' }}
                                @endif


                                @if ($months === 0)
                                    0 Months
                                @endif

                            </div>

                        </div>

                    </td>

                </tr>


                <tr>

                    <td>

                        <div class="info-box">

                            <div class="label">
                                Notice Period
                            </div>

                            <div class="value">

                                {{ $application->notice_period ?? '-' }}

                            </div>

                        </div>

                    </td>


                    <td>

                        <div class="info-box">

                            <div class="label">
                                Available From
                            </div>

                            <div class="value">

                                @if (!empty($application->available_from))
                                    {{ \Carbon\Carbon::parse($application->available_from)->format('d M Y') }}
                                @else
                                    -
                                @endif

                            </div>

                        </div>

                    </td>

                </tr>

            </table>

        </div>


        {{-- =================================================
        COMPENSATION
    ================================================== --}}

        <div class="section">

            <div class="section-title">
                Compensation
            </div>

            <table class="salary-table">

                <tr>

                    <td>

                        <div class="salary-card">

                            <div class="label">
                                Current Salary
                            </div>

                            <div class="salary-value">

                                @if (!is_null($application->current_salary) && $application->current_salary !== '')
                                    PKR
                                    {{ number_format((float) $application->current_salary) }}
                                @else
                                    -
                                @endif

                            </div>

                        </div>

                    </td>


                    <td>

                        <div class="salary-card">

                            <div class="label">
                                Expected Salary
                            </div>

                            <div class="salary-value">

                                @if (!is_null($application->expected_salary) && $application->expected_salary !== '')
                                    PKR
                                    {{ number_format((float) $application->expected_salary) }}
                                @else
                                    -
                                @endif

                            </div>

                        </div>

                    </td>


                    <td>

                        <div class="salary-card">

                            <div class="label">
                                Experience
                            </div>

                            <div class="salary-value">

                                {{ $application->month_of_exprience ?? 0 }}

                                Months

                            </div>

                        </div>

                    </td>

                </tr>

            </table>

        </div>


        {{-- =================================================
        DECLARATION
    ================================================== --}}




        {{-- =================================================
        FOOTER
    ================================================== --}}

        <div class="footer">

            Recruitment Management System

            <br>

            Application ID:
            {{ $application->id }}

            &nbsp; | &nbsp;

            Generated:
            {{ now()->format('d M Y, h:i A') }}

        </div>

    </div>

</body>

</html>
