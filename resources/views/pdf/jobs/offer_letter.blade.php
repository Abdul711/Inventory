```blade
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Appointment Letter - {{ $jobOffer->offer_number }}</title>

    <style>
        @page {
            margin: 30px 40px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #222;
        }

        .header-table,
        .meta-table,
        .detail-table,
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .logo {
            width: 90px;
            max-height: 70px;
        }

        .company-info {
            text-align: right;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
        }

        .company-info p {
            margin: 0;
            font-size: 10px;
            color: #555;
        }

        .divider {
            border-top: 2px solid #222;
            margin: 15px 0 20px;
        }

        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .meta-table {
            margin-bottom: 20px;
        }

        .text-right {
            text-align: right;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 8px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
        }

        .detail-table {
            border: 1px solid #ddd;
        }

        .detail-table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
        }

        .detail-table .label {
            width: 35%;
            font-weight: bold;
            background: #f5f5f5;
        }

        .salary-box {
            border: 1px solid #ccc;
            padding: 15px;
            margin-top: 10px;
            background: #fafafa;
        }

        .salary {
            font-size: 18px;
            font-weight: bold;
        }

        .terms {
            padding-left: 20px;
        }

        .terms li {
            margin-bottom: 7px;
        }

        .signature-table {
            margin-top: 45px;
        }

        .signature-table td {
            width: 50%;
            vertical-align: bottom;
        }

        .signature-line {
            border-top: 1px solid #222;
            width: 75%;
            padding-top: 5px;
        }

        .footer {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
    </style>
</head>

<body>

    <table class="header-table">
        <tr>
            <td>
                @if (!empty($companyLogo))
                    <img src="{{ $companyLogo }}" class="logo">
                @endif
            </td>

            <td class="company-info">
                <div class="company-name">
                    {{ config('app.name') }}
                </div>

                <p>{{ $companyAddress ?? '' }}</p>
                <p>{{ $companyEmail ?? '' }}</p>
                <p>{{ $companyPhone ?? '' }}</p>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="title">
        Appointment Letter
    </div>

    <table class="meta-table">
        <tr>
            <td>
                <strong>Reference No:</strong>
                {{ $jobOffer->offer_number }}
            </td>

            <td class="text-right">
                <strong>Date:</strong>
                {{ \Carbon\Carbon::parse($jobOffer->offer_date)->format('d M Y') }}
            </td>
        </tr>
    </table>


    <p>
        <strong>To,</strong>
    </p>

    <p>
        <strong>
            {{ $jobOffer->applicant->full_name ?? $jobOffer->applicant->name }}
        </strong>

        @if (!empty($jobOffer->applicant->address))
            <br>
            {{ $jobOffer->applicant->address }}
        @endif
    </p>


    <p>
        Dear
        <strong>
            {{ $jobOffer->applicant->full_name ?? $jobOffer->applicant->name }}
        </strong>,
    </p>

    <p>
        We are pleased to appoint you as
        <strong>
            {{ $jobOffer->jobApplication?->jobPosting?->job_title ?? ($jobOffer->designation?->name ?? 'Employee') }}
        </strong>

        @if ($jobOffer->jobApplication?->jobPosting?->department)
            in the
            <strong>
                {{ $jobOffer->jobApplication->jobPosting->department->name }}
            </strong>
            department
        @endif

        at <strong>{{ config('app.name') }}</strong>.
    </p>

    <p>
        This appointment is based on your successful completion of our recruitment
        process and acceptance of the employment offer issued under reference
        <strong>{{ $jobOffer->offer_number }}</strong>.
    </p>


    <div class="section-title">
        Appointment Details
    </div>

    <table class="detail-table">

        <tr>
            <td class="label">Employee Name</td>

            <td>
                {{ $jobOffer->applicant->full_name ?? $jobOffer->applicant->name }}
            </td>
        </tr>

        <tr>
            <td class="label">Position</td>

            <td>
                {{ $jobOffer->jobApplication?->jobPosting?->job_title ?? ($jobOffer->designation?->name ?? '-') }}
            </td>
        </tr>

        <tr>
            <td class="label">Department</td>

            <td>
                {{ $jobOffer->jobApplication?->jobPosting?->department?->name ?? '-' }}
            </td>
        </tr>

        <tr>
            <td class="label">Offer Number</td>

            <td>
                {{ $jobOffer->offer_number }}
            </td>
        </tr>

        <tr>
            <td class="label">Offer Date</td>

            <td>
                {{ \Carbon\Carbon::parse($jobOffer->offer_date)->format('d M Y') }}
            </td>
        </tr>

        @if (!empty($jobOffer->work_durations))
            <tr>
                <td class="label">Work Duration</td>

                <td>
                    {{ $jobOffer->work_durations }}
                    {{ $jobOffer->work_durations == 1 ? 'Month' : 'Months' }}
                </td>
            </tr>
        @endif

        @if (!empty($jobOffer->working_days))
            <tr>
                <td class="label">Working Days</td>

                <td>
                    {{ $jobOffer->working_days }}
                </td>
            </tr>
        @endif

        @if (!empty($jobOffer->joining_date))
            <tr>
                <td class="label">Joining Date</td>

                <td>
                    {{ \Carbon\Carbon::parse($jobOffer->joining_date)->format('d M Y') }}
                </td>
            </tr>
        @endif

        @if (!empty($jobOffer->employee_type))
            <tr>
                <td class="label">Employment Type</td>

                <td>
                    {{ ucwords(str_replace('_', ' ', $jobOffer->employee_type)) }}
                </td>
            </tr>
        @endif

        @if (!empty($jobOffer->job_mode))
            <tr>
                <td class="label">Job Mode</td>

                <td>
                    {{ ucwords(str_replace('_', ' ', $jobOffer->job_mode)) }}
                </td>
            </tr>
        @endif

    </table>


    <div class="section-title">
        Salary
    </div>

    <div class="salary-box">

        <div>
            Your approved monthly salary will be:
        </div>

        <div class="salary">
            PKR {{ number_format($jobOffer->approved_salary ?? 0, 2) }}
        </div>

        <div>
            per month, subject to applicable deductions, taxes and company policies.
        </div>

    </div>


    <div class="section-title">
        Terms and Conditions
    </div>

    <ol class="terms">

        <li>
            You will perform the duties and responsibilities associated with your
            assigned position and any additional reasonable responsibilities
            assigned by management.
        </li>

        <li>
            You are required to comply with all company policies, rules,
            procedures and standards of professional conduct.
        </li>

        <li>
            You must maintain confidentiality regarding company data, customer
            information, employee information, financial records, systems,
            software, source code and other confidential information.
        </li>

        <li>
            Your salary will be paid according to the company's payroll schedule
            and will be subject to applicable deductions and taxes.
        </li>

        <li>
            You are required to follow the company's attendance, leave, working
            hours and workplace policies.
        </li>

        <li>
            All company property, records, documents, devices and credentials
            provided to you remain the property of the company.
        </li>

        <li>
            Any false information or fraudulent documentation submitted during
            recruitment may result in disciplinary action or termination according
            to company policy and applicable law.
        </li>

        @if (!empty($jobOffer->work_durations))
            <li>
                Your employment duration under this appointment is
                <strong>
                    {{ $jobOffer->work_durations }}
                    {{ $jobOffer->work_durations == 1 ? 'month' : 'months' }}
                </strong>,
                unless otherwise extended or modified by the company.
            </li>
        @endif

    </ol>


    @if (!empty($jobOffer->notes))

        <div class="section-title">
            Additional Terms
        </div>

        @php
            $notes = is_array($jobOffer->notes) ? $jobOffer->notes : json_decode($jobOffer->notes, true);
        @endphp

        @if (is_array($notes))

            <ol class="terms">

                @foreach ($notes as $note)
                    <li>{{ $note }}</li>
                @endforeach

            </ol>
        @else
            <p>{{ $jobOffer->notes }}</p>
        @endif

    @endif


    <p>
        We welcome you to <strong>{{ config('app.name') }}</strong> and look
        forward to your valuable contribution to the organization.
    </p>


    <div class="section-title">
        Acceptance
    </div>

    <p>
        I,
        <strong>
            {{ $jobOffer->applicant->full_name ?? $jobOffer->applicant->name }}
        </strong>,
        acknowledge that I have read and understood the terms and conditions of
        this appointment letter and agree to comply with them.
    </p>


    <table class="signature-table">
        <tr>

            <td>

                <div class="signature-line">
                    <strong>Employee Signature</strong>
                </div>

                <br>

                {{ $jobOffer->applicant->full_name ?? $jobOffer->applicant->name }}

                <br><br>

                Date: __________________

            </td>

            <td>

                <div class="signature-line">
                    <strong>Authorized Signatory</strong>
                </div>

                <br>

                @if ($jobOffer->approvedBy)
                    {{ $jobOffer->approvedBy->name }}
                @else
                    Human Resources Department
                @endif

                @if ($jobOffer->approved_at)
                    <br><br>

                    Date:
                    {{ \Carbon\Carbon::parse($jobOffer->approved_at)->format('d M Y') }}
                @endif

            </td>

        </tr>
    </table>


    <div class="footer">
        {{ config('app.name') }}
        |
        Appointment Letter
        |
        {{ $jobOffer->offer_number }}
        |
        Confidential
    </div>

</body>

</html>
