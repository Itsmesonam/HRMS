<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (
    !isset($_SESSION['role']) ||
    strtolower($_SESSION['role']) !== 'landlord'
) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Payments | HRMS</title>

    <link
        rel="stylesheet"
        href="assets/css/landlord_payments_style.css"
    >

</head>

<body>

<div class="page-container">

    <header class="page-header">

        <div>

            <h1>Payments</h1>

            <p>
                View payments received from your tenants.
            </p>

        </div>

        <a
            href="landlorddashboard.php"
            class="back-btn"
        >
            ← Dashboard
        </a>

    </header>


    <main>


        <!-- SUMMARY -->

        <div class="summary-grid">

            <div class="summary-card">

                <span>
                    Total Received
                </span>

                <h2>
                    Rs. 0
                </h2>

            </div>


            <div class="summary-card">

                <span>
                    This Month
                </span>

                <h2>
                    Rs. 0
                </h2>

            </div>


            <div class="summary-card">

                <span>
                    Pending
                </span>

                <h2>
                    Rs. 0
                </h2>

            </div>

        </div>


        <!-- PAYMENTS -->

        <div class="payment-card">

            <div class="card-header">

                <h2>
                    Payment History
                </h2>

                <select>

                    <option>
                        All Payments
                    </option>

                    <option>
                        Paid
                    </option>

                    <option>
                        Pending
                    </option>

                </select>

            </div>


            <div class="empty-state">

                <div class="icon">
                    💳
                </div>

                <h3>
                    No Payments Yet
                </h3>

                <p>
                    Tenant payments will appear here.
                </p>

            </div>

        </div>


    </main>

</div>

</body>

</html>