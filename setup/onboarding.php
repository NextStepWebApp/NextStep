<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<link rel="icon" type="image/x-icon" href="../images/logo.webp"/>
<link rel="stylesheet" href="../css/style_navbar.css"/>
<link rel="stylesheet" href="../css/style_page.css"/>
<title>NextStep</title>
<style>
.onboarding-box {
    text-align: center;
    padding: 70px 20px;
}
.brand-big {
    font-size: 56px;
    font-weight: 800;
    margin-bottom: 30px;
    background: linear-gradient(135deg, #0b1d59, #2563eb);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.onboarding-actions {
    display: flex;
    justify-content: center;
    gap: 16px;
    flex-wrap: wrap;        
}
.onboarding-actions .simple-btn {
    min-width: 180px;
    max-width: 220px;
    padding: 12px 24px;
    font-size: 1rem;
    flex: 0 1 auto;
    white-space: nowrap;     
}
@media (max-width: 768px) {
    .brand-big { font-size: 40px; }
    .onboarding-actions {
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }
    .onboarding-actions .simple-btn {
        width: 240px;   
    }
}
</style>
</head>
<body>
   <div class="page-box-wide onboarding-box">
       <h1 class="brand-big">Welcome to NextStep</h1>
       <div class="onboarding-actions">
           <form method="POST" action="dbcreate.php">
             <input type="submit" class="simple-btn" name="submit" value="Take the NextStep">
           </form>
             <a href="https://github.com/NextStepWebApp/NextStep" target="_blank" class="simple-btn">Learn More</a>
       </div>
   </div> 
</body>
</html>
