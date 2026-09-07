<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>Interview Entry Pass</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 1400px;
            height: 900px;
            overflow: hidden;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #ffffff;
            color: #172033;
        }

        .pass {
            width: 1400px;
            height: 900px;
            background: #ffffff;
            position: relative;
            overflow: hidden;
        }

        /* ============================
           HEADER
        ============================ */

        .header {
            height: 190px;
            background: #14213d;
            padding: 45px 70px;

            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-title h1 {
            margin: 0;
            color: #ffffff;
            font-size: 48px;
            font-weight: 700;
            letter-spacing: 1.5px;
        }

        .header-title p {
            margin: 12px 0 0;
            color: #cbd5e1;
            font-size: 19px;
        }

        .status {
            padding: 13px 28px;

            background: #dcfce7;
            color: #15803d;

            border-radius: 50px;

            font-size: 17px;
            font-weight: 700;

            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ============================
           MAIN CONTENT
        ============================ */

        .content {
            padding: 45px 70px 25px;
        }

        .columns {
            display: flex;
            width: 100%;
        }

        .candidate {
            width: 50%;
            padding-right: 60px;
        }

        .details {
            width: 50%;
            padding-left: 60px;
            border-left: 2px solid #e2e8f0;
        }

        /* ============================
           CANDIDATE
        ============================ */

        .candidate-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .candidate-photo {
            width: 145px;
            height: 145px;

            border-radius: 14px;
            object-fit: cover;

            border: 4px solid #e2e8f0;

            margin-right: 30px;
        }

        .candidate-info {
            flex: 1;
        }

        .label {
            margin-bottom: 7px;

            color: #64748b;

            font-size: 14px;
            font-weight: 700;

            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .value {
            margin-bottom: 24px;

            color: #14213d;

            font-size: 25px;
            font-weight: 700;

            line-height: 1.2;
        }

        .candidate-name {
            margin: 0;

            color: #14213d;

            font-size: 30px;
            font-weight: 700;

            line-height: 1.2;
        }

        .application-number {
            color: #475569;

            font-size: 21px;
            font-weight: 600;
        }

        /* ============================
           INTERVIEW DETAILS
        ============================ */

        .detail-row {
            margin-bottom: 20px;
        }

        .detail-row .value {
            margin-bottom: 0;
            font-size: 23px;
        }

        /* ============================
           INSTRUCTIONS
        ============================ */

        .instructions {
            margin-top: 25px;

            padding: 22px 28px;

            background: #f8fafc;

            border: 1px solid #e2e8f0;
            border-radius: 14px;
        }

        .instructions-title {
            margin-bottom: 8px;

            color: #14213d;

            font-size: 16px;
            font-weight: 700;

            letter-spacing: 0.5px;
        }

        .instructions-text {
            color: #475569;

            font-size: 16px;
            line-height: 1.5;
        }

        /* ============================
           FOOTER
        ============================ */

        .footer {
            position: absolute;

            left: 70px;
            right: 70px;
            bottom: 0;

            height: 75px;

            border-top: 1px solid #e2e8f0;

            display: flex;
            align-items: center;
            justify-content: space-between;

            color: #64748b;

            font-size: 15px;
        }

        .pass-id {
            color: #14213d;
            font-weight: 700;
        }
    </style>
</head>

<body>

    <div class="pass">

        <div class="header">

            <div class="header-title">

                <h1>INTERVIEW ENTRY PASS</h1>

            </div>

            <div class="status">
                {{ strtoupper($interview->status ?? 'scheduled') }}
            </div>

        </div>




        <div class="content">

            <div class="columns">

                {{-- CANDIDATE INFORMATION --}}

                <div class="candidate">

                    <div class="candidate-header">

                        @if ($interview->applicant?->photo)
                            <img class="candidate-photo"
                                src="{{ public_path('storage/applicant/' . $interview->applicant->photo) }}"
                                alt="Candidate Photo">
                        @endif


                        <div class="candidate-info">

                            <div class="label">
                                Candidate
                            </div>

                            <div class="candidate-name">

                                {{ $interview->applicant?->full_name ?? 'N/A' }}

                            </div>

                        </div>

                    </div>


                    <div class="label">
                        Job Position
                    </div>

                    <div class="value">

                        {{ $interview->jobApplication?->jobPosting?->designation?->name ?? 'N/A' }}

                    </div>


                    <div class="label">
                        Application Number
                    </div>

                    <div class="application-number">

                        #{{ $interview->job_application_id }}

                    </div>

                </div>


                {{-- INTERVIEW DETAILS --}}

                <div class="details">

                    <div class="detail-row">

                        <div class="label">
                            Interview Date
                        </div>

                        <div class="value">

                            {{ \Carbon\Carbon::parse($interview->scheduled_at)->format('d M Y') }}

                        </div>

                    </div>


                    <div class="detail-row">

                        <div class="label">
                            Interview Time
                        </div>

                        <div class="value">

                            {{ \Carbon\Carbon::parse($interview->scheduled_at)->format('h:i A') }}

                        </div>

                    </div>


                    <div class="detail-row">

                        <div class="label">
                            Interview Type
                        </div>

                        <div class="value">

                            {{ ucfirst($interview->type ?? 'N/A') }}

                        </div>

                    </div>


                    <div class="detail-row">

                        <div class="label">
                            Interview Mode
                        </div>

                        <div class="value">

                            {{ ucfirst($interview->mode ?? 'N/A') }}

                        </div>

                    </div>


                    <div class="detail-row">

                        <div class="label">
                            Interviewer
                        </div>

                        <div class="value">

                            {{ $interview->interviewer?->name ?? 'N/A' }}

                        </div>

                    </div>

                </div>

            </div>


            {{-- =========================
            INSTRUCTIONS
        ========================== --}}

            <div class="instructions">

                <div class="instructions-title">
                    ENTRY INSTRUCTIONS
                </div>

                <div class="instructions-text">

                    Please present this interview entry pass at reception
                    along with a valid photo ID. Candidates are advised to
                    arrive at least 15 minutes before the scheduled interview.

                </div>

            </div>

        </div>


        {{-- =========================
        FOOTER
    ========================== --}}

        <div class="footer">

            <div>
                Generated by Recruitment Management System
            </div>

            <div class="pass-id">

                PASS ID:
                INT-{{ str_pad($interview->id, 5, '0', STR_PAD_LEFT) }}

            </div>

        </div>

    </div>

</body>

</html>
