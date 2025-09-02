<?php
// Get link_id from request parameter
$link_id = isset($_GET['link_id']) ? trim($_GET['link_id']) : '';
$total_sponsorship_amount = isset($_GET['total_sponsorship_amount']) ? trim($_GET['total_sponsorship_amount']) : '';
$sponsor_name = isset($_GET['sponsor_name']) ? trim($_GET['sponsor_name']) : '';
$sponsor_email = isset($_GET['sponsor_email']) ? trim($_GET['sponsor_email']) : '';
$sponsor_phone = isset($_GET['sponsor_phone']) ? trim($_GET['sponsor_phone']) : '';
$yearmonth = isset($_GET['yearmonth']) ? trim($_GET['yearmonth']) : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sponsorship Payment Update Form</title>
    <style>
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 200px; padding: 5px; }
    </style>
</head>
<body>
    <form action="../op/hook_GPay.php" method="POST">
        <div class="form-group">
            <label for="yearmonth">Year Month (YYYYMM):</label>
            <input type="text" id="yearmonth" name="yearmonth" value="<?php echo htmlspecialchars($yearmonth); ?>" readonly>
        </div>

        <div class="form-group">
            <label for="link_id">Link ID:</label>
            <input type="text" id="link_id" name="link_id" value="<?php echo htmlspecialchars($link_id); ?>" readonly>
        </div>

        <div class="form-group">
            <label for="sponsor_name">Sponsor Name:</label>
            <input type="text" id="sponsor_name" name="sponsor_name" value="<?php echo htmlspecialchars($sponsor_name); ?>" readonly>
        </div>

        <div class="form-group">
            <label for="sponsor_email">Sponsor Email:</label>
            <input type="text" id="sponsor_email" name="sponsor_email" value="<?php echo htmlspecialchars($sponsor_email); ?>" readonly>
        </div>

        <div class="form-group">
            <label for="sponsor_phone">Sponsor Phone:</label>
            <input type="text" id="sponsor_phone" name="sponsor_phone" value="<?php echo htmlspecialchars($sponsor_phone); ?>" readonly>
        </div>

        <div class="form-group">
            <label for="amount">Amount:</label>
            <input type="number" id="amount" name="amount" step="0.01" value="<?php echo htmlspecialchars($total_sponsorship_amount); ?>" required>
        </div>

        <div class="form-group">
            <label for="payment_ref">Payment Reference:</label>
            <input type="text" id="payment_ref" name="payment_ref" value="1234567890" required>
        </div>

        <div class="form-group">
            <label for="datetime">Payment Received Date:</label>
            <input type="date" id="datetime" name="datetime" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        
        <!-- Hidden fields -->
        <input type="hidden" name="purpose" value="SPONSORSHIP">
        <input type="hidden" name="account" value="SPN">
        <input type="hidden" name="status" value="Credit">
        
        <div class="form-group">
            <input type="submit" value="Submit" onclick="return confirm('Are you sure you want to update the sponsorship payment details?');">
        </div>
        <div class="form-group">
            <input type="button" value="Cancel" onclick="window.location.href='sponsor_dashboard.php'">
        </div>
    </form>
</body>
</html> 