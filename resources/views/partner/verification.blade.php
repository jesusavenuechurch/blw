<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Partner Verification</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .status { margin-top: 20px; font-size: 1.2rem; }
        .partner-name { font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $message }}</h1>

    <div class="status">
        Partner: <span class="partner-name">{{ $partner->full_name }}</span><br>
        Email: {{ $partner->email }}<br>
        Status: {{ $status }}
    </div>
</body>
</html>