<?php

session_start();

require_once __DIR__ . "/config/database/db.php";

/*-- Tenant Access Check --*/

if (
    !isset($_SESSION['user_id']) ||
    strtolower($_SESSION['role']) !== 'tenant'
) {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['user_id'];

/*-- Get Payment History --*/

$sql = "
    SELECT
        p.payment_id,
        p.amount,
        p.payment_method,
        p.payment_status,
        p.transaction_id,
        p.payment_date,
        p.notes,

        pr.property_name,
        pr.property_type,
        pr.location,

        b.booking_id

    FROM payments p

    INNER JOIN properties pr
        ON p.property_id = pr.property_id

    INNER JOIN bookings b
        ON p.booking_id = b.booking_id

    WHERE p.tenant_id = ?

    ORDER BY p.payment_date DESC
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $tenant_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Payment History | HRMS</title>

    <link rel="stylesheet"
          href="/hrms/Assets/css/tenant_payments_style.css">

</head>

<body>

<div class="payment-container">

    <!-- =========================================
         HEADER
    ========================================== -->

    <div class="page-header">

        <div>

            <h1>Payment History</h1>

            <p>
                View all your rental payment transactions.
            </p>

        </div>

        <a
            href="tenantdashboard.php"
            class="back-btn"
        >
            ← Dashboard
        </a>

    </div>


    <!-- =========================================
         PAYMENT TABLE
    ========================================== -->

    <div class="payment-card">

        <?php if (mysqli_num_rows($result) > 0): ?>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>Payment ID</th>

                            <th>Property</th>

                            <th>Amount</th>

                            <th>Method</th>

                            <th>Transaction ID</th>

                            <th>Date</th>

                            <th>Status</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php while ($payment = mysqli_fetch_assoc($result)): ?>

                        <tr>

                            <td>
                                #<?php
                                echo htmlspecialchars(
                                    $payment['payment_id']
                                );
                                ?>
                            </td>


                            <td>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $payment['property_name']
                                    );
                                    ?>
                                </strong>

                                <small>
                                    <?php
                                    echo htmlspecialchars(
                                        $payment['property_type']
                                    );
                                    ?>
                                    ·
                                    <?php
                                    echo htmlspecialchars(
                                        $payment['location']
                                    );
                                    ?>
                                </small>

                            </td>


                            <td>

                                <strong>
                                    Rs.
                                    <?php
                                    echo number_format(
                                        $payment['amount']
                                    );
                                    ?>
                                </strong>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $payment['payment_method']
                                );
                                ?>

                            </td>


                            <td>

                                <?php if (
                                    !empty($payment['transaction_id'])
                                ): ?>

                                    <?php
                                    echo htmlspecialchars(
                                        $payment['transaction_id']
                                    );
                                    ?>

                                <?php else: ?>

                                    <span class="no-transaction">
                                        —
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $payment['payment_date']
                                    )
                                );
                                ?>

                                <small>
                                    <?php
                                    echo date(
                                        "h:i A",
                                        strtotime(
                                            $payment['payment_date']
                                        )
                                    );
                                    ?>
                                </small>

                            </td>


                            <td>

                                <?php
                                $status = strtolower(
                                    $payment['payment_status']
                                );
                                ?>

                                <span
                                    class="status <?php echo $status; ?>"
                                >
                                    <?php
                                    echo htmlspecialchars(
                                        $payment['payment_status']
                                    );
                                    ?>
                                </span>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <!-- =========================================
                 NO PAYMENT
            ========================================== -->

            <div class="no-payment">

                <div class="payment-icon">
                    💳
                </div>

                <h2>No Payment History</h2>

                <p>
                    You have not made any rental payments yet.
                </p>

                <a
                    href="payment.php"
                    class="payment-btn"
                >
                    Make Payment
                </a>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>

<?php

mysqli_stmt_close($stmt);

?>