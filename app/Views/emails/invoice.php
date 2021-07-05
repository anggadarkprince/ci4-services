<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice Waiting For Payment</title>
</head>
<body style="padding: 20px; font-family: sans-serif">
<h1>Hi, <?= esc($user->name ?? '') ?></h1>
<p>
    Your invoice #<?= esc($invoice->no_invoice ?? '') ?> is waiting for payment
    <strong><?= esc(number_format($invoice->amount ?? 0, 2, ',', '.')) ?></strong>
</p>
</body>
</html>