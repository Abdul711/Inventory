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
        Offer Letter
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
        We are pleased to offer you a position as
        <strong>
            {{ $jobOffer->jobApplication?->jobPosting?->designation->name ?? ($jobOffer->designation?->name ?? 'Employee') }}
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
            <td class="label">Offer Expiry Date</td>

            <td>
                {{ \Carbon\Carbon::parse($jobOffer->expiry_date)->format('d M Y') }}
            </td>
        </tr>

        @if (!empty($jobOffer->duty_durations))
            <tr>
                <td class="label">Duty Duration</td>

                <td>
                    {{ $jobOffer->duty_durations }}
                    {{ $jobOffer->duty_durations == 1 ? 'Hour' : 'Hours' }}
                </td>
            </tr>
        @endif

        @if (!empty($jobOffer->working_days))
            <tr>
                <td class="label">Working Days</td>

                <td>
                    <ol>
                        @foreach ($jobOffer->working_days as $working_day)
                            <li> {{ $working_day }}</li>
                        @endforeach
                    </ol>
                </td>
            </tr>
        @endif



        @if (!empty($jobOffer->jobApplication->jobPosting->employment_type))
            <tr>
                <td class="label">Employment Type</td>

                <td>
                    {{ str()->headline($jobOffer->jobApplication->jobPosting->employment_type) }}
                </td>
            </tr>
        @endif

        @if (!empty($jobOffer->jobApplication->jobPosting->work_mode))
            <tr>
                <td class="label">Job Mode</td>

                <td>
                    {{ str()->headline($jobOffer->jobApplication->jobPosting->work_mode) }}
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
            PKR {{ number_format($jobOffer->approved_salary ?? $jobOffer->candidate_expected_salary, 2) }}
        </div>

        <div>
            per month, subject to applicable deductions, taxes and company policies.
        </div>

    </div>


    <div class="section-title">
        Terms and Conditions
    </div>
    @php
        $terms = $jobOffer->terms_conditions;
    @endphp
    <ol class="terms">
        @if (empty($terms))
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
                and other confidential information.
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
        @else
            @foreach ($terms as $term)
                <li>
                    {{ $term }}
                </li>
            @endforeach
        @endif


        {{--
        @if (!empty($jobOffer->duty_durations))
            <li>
                Your employment duration under this appointment is
                <strong>
                    {{ $jobOffer->duty_durations }}
                    {{ $jobOffer->duty_durations == 1 ? 'hour' : 'hours' }}
                </strong>,
                unless otherwise extended or modified by the company.
            </li>
        @endif

        --}}
        <li>
            If you accepted the offer please returned the signed letter to HR department before
            <strong> {{ \Carbon\Carbon::parse($jobOffer->expiry_date)->format('d M Y') }}
            </strong>
            otherwise offer will be cancelled.

        </li>
        <li> Your employment will be subject to the probation period specified in your offer letter. Your performance,
            conduct, attendance, and suitability for the position may be evaluated during this period.
        </li>
        <li>
            You will be entitled to leaves, holidays, and other time-off benefits in accordance with company policy and
            applicable employment laws.
        </li>
        <li>
            You are expected to maintain professional behavior and must not engage in harassment, discrimination, fraud,
            misconduct, violence, or any activity that violates company policy or applicable law.
        </li>
        <li>
            Either you or the company may terminate the employment relationship by providing the applicable notice
            period
            specified in your offer letter, employment agreement, company policy, and applicable law.
        </li>
        <li>
            Upon resignation, termination, or completion of employment, you must return all company property, documents,
            equipment, devices, access cards, credentials, records, and other company resources in your possession.
        </li>
        <li>
            You must protect company passwords, system credentials, and other access information and must not share them
            with unauthorized persons.
        </li>
        <li>

            Your employment and continued employment may be subject to verification of educational qualifications,
            previous employment, references, identity documents, and other information provided during recruitment,
            where permitted
            by applicable law.
        </li>
        <li>
            Any changes to your designation, department, duties, salary, benefits, working arrangements, or other
            employment
            conditions will be handled in accordance with company policy, the employment agreement, and applicable law.
        </li>
        <li>
            By signing and returning this offer letter, you confirm that you have read, understood, and accepted the
            terms
            and conditions of employment stated in this letter.
        </li>
    </ol>
    <br>



    @if (!empty($jobOffer->benefits))

        <div class="section-title">
            Benefits
        </div>

        @php
            $notes = is_array($jobOffer->benefits) ? $jobOffer->benefits : json_decode($jobOffer->benefits, true);
        @endphp

        @if (is_array($notes))

            <ol class="terms">

                @foreach ($notes as $note)
                    <li>{{ $note }}</li>
                @endforeach

            </ol>
        @else
            <p>{{ $jobOffer->benefits }}</p>
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
