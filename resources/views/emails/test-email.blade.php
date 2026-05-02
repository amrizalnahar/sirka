<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test Email</title>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; background-color: #f3f4f6; margin: 0; padding: 0; }
        .container { max-width: 480px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .header { background-color: #1A6FAA; padding: 32px 24px; text-align: center; }
        .header h1 { color: #ffffff; font-size: 20px; margin: 0; }
        .content { padding: 32px 24px; color: #374151; font-size: 14px; line-height: 1.6; }
        .content p { margin: 0 0 16px; }
        .footer { padding: 24px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ \App\Models\SiteSetting::getValue('site_name', config('app.name')) }}</h1>
        </div>
        <div class="content">
            <p>Halo,</p>
            <p>Ini adalah email tes dari Admin Panel <strong>{{ \App\Models\SiteSetting::getValue('site_name', config('app.name')) }}</strong>.</p>
            <p>Jika Anda menerima email ini, berarti konfigurasi SMTP berfungsi dengan baik.</p>
        </div>
        <div class="footer">
            Email ini dikirim secara otomatis oleh sistem.
        </div>
    </div>
</body>
</html>
