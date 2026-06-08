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
<title>NextStep — Message from <?php echo htmlspecialchars($school_name); ?></title>
</head>
<body style="margin:0;padding:0;background-color:#f3f6fb;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f3f6fb;padding:30px 15px;">
    <tr>
        <td align="center">

            <table width="600" cellpadding="0" cellspacing="0" border="0" 
            style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;table-layout:fixed;">

                <!-- ───────────── Header ───────────── -->
                <tr>
                    <td align="center" style="background:#1e3a8a;padding:40px 30px;">
                        <div style="font-size:42px;font-weight:800;color:#ffffff;font-family:Arial,sans-serif;margin-bottom:10px;">
                            NextStep
                        </div>
                        <div style="font-size:16px;color:#dbeafe;font-family:Arial,sans-serif;">
                            <?php echo htmlspecialchars($school_name); ?>
                        </div>
                    </td>
                </tr>

                <!-- ───────────── Sent-by banner ───────────── -->
                <tr>
                    <td style="background:#eff6ff;border-bottom:1px solid #bfdbfe;padding:16px 30px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="font-family:Arial,sans-serif;font-size:13px;color:#64748b;">
                                    MESSAGE FROM
                                </td>
                            </tr>
                            <tr>
                                <td style="font-family:Arial,sans-serif;font-size:18px;font-weight:700;color:#1e3a8a;padding-top:4px;">
                                    <?php echo htmlspecialchars($smtp_username); ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-family:Arial,sans-serif;font-size:13px;color:#64748b;padding-top:2px;">
                                    <?php echo htmlspecialchars($school_name); ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- ───────────── Body message ───────────── -->
                <tr>
                <td style="padding:20px 30px 0 30px;">
                <h1 style="margin:0 0 8px 0;color:#0f172a;font-size:26px;font-family:Arial,sans-serif;">
                    <?php echo htmlspecialchars($mail_subject); ?> 
                </h1>
                <div style="margin:0;color:#334155;font-size:15px;line-height:1.7;font-family:Arial,sans-serif;">
                <?php echo $mail_body; ?>
                </div>
                </td>
                </td>
                </tr>            
              
                <!-- ───────────── Applied filters ───────────── -->
                <?php if (!empty($filters)): ?>
                <tr>
                    <td style="padding:30px 30px 30px 30px;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
                <tr>
                    <td style="background:#f1f5f9;border-bottom:1px solid #e2e8f0;padding:12px 18px;">
                        <span style="font-family:Arial,sans-serif;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;">
                            Search Filters Applied
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:14px 18px;font-family:Arial,sans-serif;font-size:13px;color:#334155;line-height:1.6;">
                        <?php echo htmlspecialchars($filters); ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
<?php endif; ?>
                
                <!-- ───────────── Alumni info card ───────────── -->
                <?php if (!empty($alumni_data)): ?>
                <tr>
                    <td style="padding:30px 30px 30px 30px;">

                        <!-- Section label -->
                        <p style="margin:0 0 14px 0;font-family:Arial,sans-serif;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;">
                            Your Information
                        </p>

                        <!-- Card -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">

                            <!-- Info rows -->
                            <tr>
                                <td style="padding:18px 18px 0 18px;">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">

                                        <?php
                                        foreach ($alumni_data as $data):
                                            if (empty($data)) {
                                                continue;
                                            }
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($data); ?></td>
                                        </tr> 
                                        <?php endforeach; ?>

                                    </table>
                                </td>
                            </tr>

                            <!-- Update prompt -->
                            <tr>
                                <td style="background:#eff6ff;border-top:1px solid #bfdbfe;padding:16px 18px;">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td style="font-family:Arial,sans-serif;font-size:13px;color:#334155;line-height:1.5;padding-right:16px;vertical-align:middle;">
                                                Has something changed? Keep your profile up to date so your school can connect you with the right opportunities.
                                            </td>
                                            <td style="vertical-align:middle;white-space:nowrap;">
                                                <a href="<?php echo htmlspecialchars($update_url); ?>"
                                                   style="display:inline-block;background:#1e3a8a;color:#ffffff;font-family:Arial,sans-serif;font-size:13px;font-weight:700;text-decoration:none;padding:10px 18px;border-radius:7px;">
                                                    Update Info
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
                <?php endif; ?>

                <!-- ───────────── Footer ───────────── -->
                <tr>
                    <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:25px;text-align:center;margin-top:30px;">
                        <p style="margin:0 0 15px 0;color:#64748b;font-size:13px;line-height:1.5;font-family:Arial,sans-serif;">
                            This email was sent to you by <?php echo htmlspecialchars($smtp_username); ?>
                            via NextStep at <?php echo htmlspecialchars($school_name); ?>.
                            If you believe this was sent in error, please disregard it.
                        </p>
                        <p style="margin:0 0 15px 0;font-family:Arial,sans-serif;">
                            <a href="https://github.com/NextStepWebApp/NextStep" style="color:#2563eb;text-decoration:none;font-weight:bold;">GitHub</a>
                            &nbsp;|&nbsp;
                            <a href="https://www.youtube.com/@MelchizedekShah" style="color:#2563eb;text-decoration:none;font-weight:bold;">YouTube</a>
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>