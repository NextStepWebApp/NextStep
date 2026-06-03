<?php
if (!defined("SECURE_ACCESS")) {
    exit('Direct access not permitted');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NextStep Verification</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f6fb;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f3f6fb;padding:30px 15px;">
    <tr>
        <td align="center">

            <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;">

                <!-- Header -->
                <tr>
                    <td align="center"
                        style="background:#1e3a8a;padding:40px 30px;">

                        <div style="
                            font-size:42px;
                            font-weight:800;
                            color:#ffffff;
                            font-family:Arial,sans-serif;
                            margin-bottom:10px;
                        ">
                            NextStep
                        </div>

                        <div style="
                            font-size:16px;
                            color:#dbeafe;
                            font-family:Arial,sans-serif;
                        ">
                            <?php echo htmlspecialchars($school_name); ?>
                        </div>

                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td align="center" style="padding:40px 30px;">

                        <h1 style="
                            margin:0 0 20px 0;
                            color:#0f172a;
                            font-size:30px;
                            font-family:Arial,sans-serif;
                        ">
                            Verify Your Email Settings
                        </h1>

                        <p style="
                            margin:0 0 30px 0;
                            color:#64748b;
                            font-size:16px;
                            line-height:1.6;
                            font-family:Arial,sans-serif;
                        ">
                            Use the verification code below to complete your
                            email settings registration.
                        </p>

                        <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center"
                                    style="
                                        background:#eff6ff;
                                        border:2px solid #bfdbfe;
                                        border-radius:10px;
                                        padding:20px 35px;
                                        font-size:42px;
                                        font-weight:700;
                                        letter-spacing:8px;
                                        color:#1e3a8a;
                                        font-family:Courier New, monospace;
                                    ">
                                    <?php echo htmlspecialchars($verification_code); ?>
                                </td>
                            </tr>
                        </table>

                        <p style="
                            margin-top:30px;
                            color:#64748b;
                            font-size:14px;
                            line-height:1.6;
                            font-family:Arial,sans-serif;
                        ">
                            Enter this code into NextStep to verify your SMTP
                            settings.
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="
                        background:#f8fafc;
                        border-top:1px solid #e2e8f0;
                        padding:25px;
                        text-align:center;
                    ">

                        <p style="
                            margin:0 0 15px 0;
                            color:#64748b;
                            font-size:13px;
                            line-height:1.5;
                            font-family:Arial,sans-serif;
                        ">
                            If you didn't request this verification code,
                            you can safely ignore this email.
                        </p>

                        <p style="
                            margin:0 0 15px 0;
                            font-family:Arial,sans-serif;
                        ">
                            <a href="https://github.com/NextStepWebApp/NextStep"
                               style="color:#2563eb;text-decoration:none;font-weight:bold;">
                                GitHub
                            </a>

                            &nbsp;|&nbsp;

                            <a href="https://www.youtube.com/@MelchizedekShah"
                               style="color:#2563eb;text-decoration:none;font-weight:bold;">
                                YouTube
                            </a>
                        </p>

                        <p style="
                            margin:0;
                            color:#94a3b8;
                            font-size:12px;
                            font-family:Arial,sans-serif;
                        ">
                            © <?php echo date('Y'); ?> NextStep
                        </p>

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
