```blade
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        Appointment Letter - {{ $employee->employee_code ?? $employee->id }}
    </title>

    <style>
        @page {
            margin: 30px 40px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
            line-height: 1.6;
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
            font-size: 21px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .meta-table {
            margin-bottom: 20px;
        }

        .meta-table td {
            padding: 2px 0;
        }

        .text-right {
            text-align: right;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #ccc;
        }

        .detail-table {
            border: 1px solid #ddd;
            margin: 10px 0;
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
            margin: 12px 0;
            background: #fafafa;
        }

        .salary {
            font-size: 17px;
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
            width: 75%;
            border-top: 1px solid #222;
            padding-top: 5px;
        }

        .footer {
            position: fixed;
            bottom: -12px;
            left: 0;
            right: 0;
            border-top: 1px solid #ddd;
            padding-top: 5px;
            text-align: center;
            color: #777;
            font-size: 9px;
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

                <p>{{ $companyAddress ?? 'Company Address' }}</p>
                <p>{{ $companyEmail ?? 'hr@example.com' }}</p>
                <p>{{ $companyPhone ?? '+92 XXX XXXXXXX' }}</p>
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

                {{ $appointmentLetter->reference_number ?? 'APT-' . str_pad($employee->id, 6, '0', STR_PAD_LEFT) }}
            </td>

            <td class="text-right">
                <strong>Date:</strong>

                {{ isset($appointmentLetter->appointment_date)
                    ? \Carbon\Carbon::parse($appointmentLetter->appointment_date)->format('d M Y')
                    : now()->format('d M Y') }}
            </td>
        </tr>
    </table>


    <p>
        <strong>To,</strong>
    </p>

    <p>
        <strong>
            {{ $employee->full_name ?? $employee->name }}
        </strong>

        <br>

        @if (!empty($employee->address))
            {{ $employee->address }}
        @endif
    </p>


    <p>
        Dear
        <strong>
            {{ $employee->full_name ?? $employee->name }}
        </strong>,
    </p>

    <p>
        We are pleased to appoint you as
        <strong>
            {{ $employee->designation?->name ?? ($employee->designation_name ?? 'Employee') }}
        </strong>
        in the
        <strong>
            {{ $employee->department?->name ?? 'Department' }}
        </strong>
        of <strong>{{ config('app.name') }}</strong>.
    </p>

    <p>
        Your appointment is based on the successful completion of our recruitment
        process and your acceptance of the employment offer. Your employment will
        be governed by the terms and conditions mentioned in this letter and by the
        policies of the company.
    </p>


    <div class="section-title">
        Appointment Details
    </div>

    <table class="detail-table">

        <tr>
            <td class="label">Employee Name</td>

            <td>
                {{ $employee->full_name ?? $employee->name }}
            </td>
        </tr>

        <tr>
            <td class="label">Employee Code</td>

            <td>
                {{ $employee->employee_code ?? '-' }}
            </td>
        </tr>

        <tr>
            <td class="label">Designation</td>

            <td>
                {{ $employee->designation?->name ?? '-' }}
            </td>
        </tr>

        <tr>
            <td class="label">Department</td>

            <td>
                {{ $employee->department?->name ?? '-' }}
            </td>
        </tr>

        @if ($employee->shift)
            <tr>
                <td class="label">Shift</td>

                <td>
                    {{ $employee->shift->name }}
                </td>
            </tr>
        @endif

        @if (!empty($employee->employee_type))
            <tr>
                <td class="label">Employment Type</td>

                <td>
                    {{ ucwords(str_replace('_', ' ', $employee->employee_type)) }}
                </td>
            </tr>
        @endif

        @if (!empty($employee->job_mode))
            <tr>
                <td class="label">Job Mode</td>

                <td>
                    {{ ucwords(str_replace('_', ' ', $employee->job_mode)) }}
                </td>
            </tr>
        @endif

        @if (!empty($employee->joining_date))
            <tr>
                <td class="label">Date of Joining</td>

                <td>
                    {{ \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') }}
                </td>
            </tr>
        @endif

        @if (!empty($employee->probation_months))
            <tr>
                <td class="label">Probation Period</td>

                <td>
                    {{ $employee->probation_months }}
                    {{ $employee->probation_months == 1 ? 'Month' : 'Months' }}
                </td>
            </tr>
        @endif

        @if (!empty($employee->reportingManager))
            <tr>
                <td class="label">Reporting Manager</td>

                <td>
                    {{ $employee->reportingManager->name }}
                </td>
            </tr>
        @endif

    </table>


    <div class="section-title">
        Compensation
    </div>

    <div class="salary-box">

        <div>
            Your agreed gross monthly salary will be:
        </div>

        <div class="salary">
            PKR {{ number_format($employee->salary ?? ($jobOffer->approved_salary ?? 0), 2) }}
        </div>

        <div>
            per month, subject to applicable taxes, deductions and company policies.
        </div>

    </div>


    <div class="section-title">
        Terms and Conditions
    </div>

    <ol class="terms">

        <li>
            Your employment will commence from
            <strong>
                {{ !empty($employee->joining_date) ? \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') : '-' }}
            </strong>.
        </li>

        @if (!empty($employee->probation_months))
            <li>
                You will initially remain on probation for a period of
                <strong>
                    {{ $employee->probation_months }}
                    {{ $employee->probation_months == 1 ? 'month' : 'months' }}
                </strong>.
                Your performance and conduct may be reviewed during this period.
            </li>
        @endif

        <li>
            You will perform all duties and responsibilities associated with your
            designation and any other reasonable duties assigned by your reporting
            manager or management.
        </li>

        <li>
            You are required to follow the company's attendance, working hours,
            leave, discipline, security and workplace policies.
        </li>

        <li>
            Your salary and other applicable benefits will be processed according
            to the company's payroll policies and applicable laws.
        </li>

        <li>
            You must maintain strict confidentiality regarding the company's
            business information, customers, employees, financial records,
            systems, source code, documents and other confidential information.
        </li>

        <li>
            Company property, documents, equipment, credentials and confidential
            information provided to you must be returned upon separation from the
            company.
        </li>

        <li>
            Any information or documentation provided by you during recruitment
            must be accurate and genuine. False or misleading information may
            result in disciplinary action or termination in accordance with
            company policy and applicable law.
        </li>

        <li>
            Your employment may be terminated or resigned from according to the
            notice period, employment agreement, company policies and applicable
            employment laws.
        </li>

    </ol>


    @if (!empty($appointmentLetter->terms))

        <div class="section-title">
            Additional Conditions
        </div>

        @php
            $terms = is_array($appointmentLetter->terms)
                ? $appointmentLetter->terms
                : json_decode($appointmentLetter->terms, true);
        @endphp

        @if (is_array($terms))

            <ol class="terms">

                @foreach ($terms as $term)
                    <li>{{ $term }}</li>
                @endforeach

            </ol>
        @else
            <p>
                {{ $appointmentLetter->terms }}
            </p>

        @endif

    @endif


    <p>
        We welcome you to <strong>{{ config('app.name') }}</strong> and look
        forward to your contribution and professional growth with the organization.
    </p>


    <div class="section-title">
        Employee Acceptance
    </div>

    <p>
        I,
        <strong>{{ $employee->full_name ?? $employee->name }}</strong>,
        confirm that I have read and understood this appointment letter and agree
        to the terms and conditions stated above.
    </p>


    <table class="signature-table">

        <tr>

            <td>

                <div class="signature-line">
                    <strong>Employee Signature</strong>
                </div>

                <br>

                Name:
                {{ $employee->full_name ?? $employee->name }}

                <br>

                Date: ______________________

            </td>


            <td>

                <div class="signature-line">
                    <strong>Authorized Signatory</strong>
                </div>

                <br>

                @if (!empty($appointmentLetter->approvedBy))
                    {{ $appointmentLetter->approvedBy->name }}
                @else
                    Human Resources Department
                @endif

                <br>

                Date:
                {{ !empty($appointmentLetter->approved_at)
                    ? \Carbon\Carbon::parse($appointmentLetter->approved_at)->format('d M Y')
                    : '______________________' }}

            </td>

        </tr>

    </table>


    <div class="footer">
        {{ config('app.name') }}
        |
        Appointment Letter
        |
        {{ $employee->employee_code ?? $employee->id }}
        |
        Confidential
    </div>

</body>

</html>
```
