<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($code . ' - ' . $heading) ?></title>
<style>
  body { font-family: 'Segoe UI', system-ui, sans-serif; background: linear-gradient(135deg,#f4f5fb,#eae7fb); margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; color:#1f2330; }
  .box { text-align:center; padding: 2rem; max-width: 460px; }
  .code { font-size: 4rem; font-weight: 800; background: linear-gradient(135deg,#5b3df6,#4527d6); -webkit-background-clip:text; background-clip:text; color:transparent; }
  h1 { font-size: 1.3rem; margin: .25rem 0 .5rem; }
  p { color:#6b7280; }
  a.btn { display:inline-block; margin-top:1rem; padding:.6rem 1.4rem; background:#5b3df6; color:#fff; text-decoration:none; border-radius:8px; font-weight:600; }
</style>
</head>
<body>
  <div class="box">
    <div class="code"><?= htmlspecialchars((string) $code) ?></div>
    <h1><?= htmlspecialchars($heading) ?></h1>
    <p><?= htmlspecialchars($message) ?></p>
    <a class="btn" href="/dashboard">Go to Dashboard</a>
  </div>
</body>
</html>
