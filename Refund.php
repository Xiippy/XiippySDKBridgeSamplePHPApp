<?php



require_once __DIR__ . '/vendor/autoload.php';
require 'constants.php';
use \Xiippy\POSeComSDK\Light\XiippySDKBridgeApiClient;
use \Xiippy\POSeComSDK\Light\Models\RefundCardPaymentRequest;





$successMessage = null;
$errorMessage = null;
$statementID = '';
$statementTimeStamp = '';
$amount = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statementID = $_POST['StatementID'] ?? '';
    $statementTimeStamp = $_POST['StatementTimeStamp'] ?? '';
    $amount = $_POST['Amount'] ?? null;

    // Validation (add more robust validation as needed)
    if (empty($statementID) || empty($statementTimeStamp)) {
        $errorMessage = "Statement ID and Statement Timestamp are required.";
    } else {
        // Simulate processing the refund
        try {
            // Process the refund logic here (e.g., call a backend service)
            $response = RefundCardPayment($statementID, $statementTimeStamp, $amount);
            $successMessage = "Refund was processed successfully. Response:\n" . json_encode($response, JSON_PRETTY_PRINT);

        } catch (Exception $e) {
            $errorMessage = "An error occurred: " . $e->getMessage();
        }
    }
}

function RefundCardPayment($statementID, $statementTimeStamp, $amount)
{

    $req = new RefundCardPaymentRequest();


    $req->MerchantGroupID = MerchantGroupID;
    $req->MerchantID = MerchantID;
    $req->RandomStatementID = $statementID;
    $req->StatementTimestamp = $statementTimeStamp;
    if (!is_null($amount) && !empty(trim($amount)))
        $req->AmountInDollars = $amount;


    // instantiate the SDK objects and feed them with the right parameters
    $client = new XiippySDKBridgeApiClient(true, Config_ApiKey, Config_BaseAddress, MerchantID, MerchantGroupID);
    // initiate the payment
    $response = $client->RefundCardPayment($req);
    return $response;
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>XiippySDKBridgeSamplePHPApp:: Refund</title>
    <link rel="stylesheet" href="/lib/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/css/site.css" />
</head>

<body>



    <header>
        <nav class="navbar navbar-expand-sm navbar-toggleable-sm navbar-light bg-white border-bottom box-shadow mb-3">
            <div class="container">
                <a class="navbar-brand" href="/">XiippySDKBridgeSamplePhpApp</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target=".navbar-collapse"
                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="navbar-collapse collapse d-sm-inline-flex justify-content-between">
                    <ul class="navbar-nav flex-grow-1">
                        <li class="nav-item">
                            <a class="nav-link text-dark" asp-area="" asp-page="/Index">Home</a>
                        </li>

                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="container mt-5">
        <main role="main" class="pb-3">


            <h2>Refund Form</h2>

            <form method="post" class="needs-validation">
                <div class="mb-3">
                    <label for="StatementID" class="form-label">Statement ID</label>
                    <input type="text" id="StatementID" name="StatementID" class="form-control"
                        value="<?= htmlspecialchars($statementID) ?>" required>
                    <?php if (!empty($errorMessage) && empty($statementID)): ?>
                        <span class="text-danger">Statement ID is required.</span>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="StatementTimeStamp" class="form-label">Statement TimeStamp</label>
                    <input type="text" id="StatementTimeStamp" name="StatementTimeStamp" class="form-control"
                        value="<?= htmlspecialchars($statementTimeStamp) ?>" required>
                    <?php if (!empty($errorMessage) && empty($statementTimeStamp)): ?>
                        <span class="text-danger">Statement TimeStamp is required.</span>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="Amount" class="form-label">Amount (leave blank to refund full amount)</label>
                    <input type="number" id="Amount" name="Amount" step="0.01" class="form-control"
                        value="<?= htmlspecialchars($amount) ?>">
                </div>

                <button type="submit" class="btn btn-primary">Submit</button>
            </form>

            <?php if ($successMessage): ?>
                <div class="mt-3 alert alert-success">
                    <h5>Success</h5>
                    <p><?= htmlspecialchars($successMessage) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($errorMessage): ?>
                <div class="mt-3 alert alert-danger">
                    <h5>Error</h5>
                    <p><?= htmlspecialchars($errorMessage) ?></p>
                </div>
            <?php endif; ?>



        </main>
    </div>

    <footer class="border-top footer text-muted">
        <div class="container">
            &copy; 2024 - XiippySDKBridgeSamplePhpApp - <a asp-area="" asp-page="/Privacy">Privacy</a>
        </div>
    </footer>

    <script src="lib/jquery/dist/jquery.min.js"></script>
    <script src="lib/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/site.js" asp-append-version="true"></script>

</body>

</html>