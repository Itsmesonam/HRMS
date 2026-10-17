<?php

session_start();

require_once __DIR__ . "/config/database/db.php";


/* =====================================================
   ADMIN LOGIN CHECK
===================================================== */

if (!isset($_SESSION['admin_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: adminlogin.php");
    exit();
}


/* =====================================================
   SEARCH
===================================================== */

$search = trim($_GET['search'] ?? '');


/* =====================================================
   MESSAGE QUERY
===================================================== */

$sql = "
    SELECT
        m.message_id,
        m.sender_id,
        m.receiver_id,
        m.property_id,
        m.booking_id,
        m.message,
        m.message_status,
        m.sent_at,

        sender.firstname AS sender_firstname,
        sender.lastname AS sender_lastname,

        receiver.firstname AS receiver_firstname,
        receiver.lastname AS receiver_lastname,

        p.property_name

    FROM messages m

    LEFT JOIN users sender
        ON m.sender_id = sender.id

    LEFT JOIN users receiver
        ON m.receiver_id = receiver.id

    LEFT JOIN properties p
        ON m.property_id = p.property_id
";


$params = [];
$types = "";


/* =====================================================
   SEARCH FILTER
===================================================== */

if ($search !== '') {

    $sql .= "
        WHERE
            m.message LIKE ?
            OR sender.firstname LIKE ?
            OR sender.lastname LIKE ?
            OR receiver.firstname LIKE ?
            OR receiver.lastname LIKE ?
            OR p.property_name LIKE ?
    ";

    $term = "%" . $search . "%";

    $params = [
        $term,
        $term,
        $term,
        $term,
        $term,
        $term
    ];

    $types = "ssssss";
}


/* =====================================================
   ORDER
===================================================== */

$sql .= "
    ORDER BY m.sent_at DESC, m.message_id DESC
";


$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {

    die(
        "Unable to load messages. Please check the messages table structure."
    );

}


if (!empty($params)) {

    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );

}


mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


/* =====================================================
   MESSAGE COUNT
===================================================== */

$messageCount = mysqli_num_rows($result);


/* =====================================================
   ADMIN NAME
===================================================== */

$adminName = $_SESSION['admin_name'] ?? 'Admin';

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Messages | HRMS Admin</title>


    <!-- Material Symbols -->

    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"
    >


    <!-- Page CSS -->

    <link
        rel="stylesheet"
        href="Assets/css/admin_messages_style.css"
    >

</head>


<body>


<div class="container">


    <!-- =================================================
         SIDEBAR
    ================================================== -->

    <aside>


        <div class="top">

            <h2 class="logo-text">
                HRMS
            </h2>

        </div>


        <nav class="sidebar">


            <a href="admindashboard.php">

                <span class="material-symbols-outlined">
                    dashboard
                </span>

                <h3>
                    Dashboard
                </h3>

            </a>


            <a href="admin_houses.php">

                <span class="material-symbols-outlined">
                    home
                </span>

                <h3>
                    Houses
                </h3>

            </a>


            <a href="admin_landlords.php">

                <span class="material-symbols-outlined">
                    real_estate_agent
                </span>

                <h3>
                    Landlords
                </h3>

            </a>


            <a href="admin_tenants.php">

                <span class="material-symbols-outlined">
                    group
                </span>

                <h3>
                    Tenants
                </h3>

            </a>


            <a href="admin_bookings.php">

                <span class="material-symbols-outlined">
                    calendar_month
                </span>

                <h3>
                    Bookings
                </h3>

            </a>


            <a href="admin_payments.php">

                <span class="material-symbols-outlined">
                    payments
                </span>

                <h3>
                    Payments
                </h3>

            </a>


            <a href="admin_reports.php">

                <span class="material-symbols-outlined">
                    bar_chart
                </span>

                <h3>
                    Reports
                </h3>

            </a>


            <a href="admin_users.php">

                <span class="material-symbols-outlined">
                    manage_accounts
                </span>

                <h3>
                    Users
                </h3>

            </a>


            <a
                href="admin_messages.php"
                class="active"
            >

                <span class="material-symbols-outlined">
                    chat
                </span>

                <h3>
                    Messages
                </h3>

            </a>


            <a href="admin_settings.php">

                <span class="material-symbols-outlined">
                    settings
                </span>

                <h3>
                    Settings
                </h3>

            </a>


            <a
                href="logout.php"
                class="logout"
            >

                <span class="material-symbols-outlined">
                    logout
                </span>

                <h3>
                    Logout
                </h3>

            </a>


        </nav>

    </aside>



    <!-- =================================================
         MAIN
    ================================================== -->

    <main>


        <!-- =================================================
             HEADER
        ================================================== -->

        <header class="page-header">


            <div>

                <h1>
                    Messages
                </h1>

                <p>
                    Monitor communication between tenants and landlords.
                </p>

            </div>


            <div class="profile">

                <?= htmlspecialchars($adminName) ?>

                · Administrator

            </div>


        </header>



        <!-- =================================================
             MESSAGE PANEL
        ================================================== -->

        <section class="panel">


            <!-- SEARCH AREA -->

            <div class="panel-top">


                <div>

                    <h2>
                        Message Records
                    </h2>

                    <p>
                        <?= number_format($messageCount) ?>
                        message(s) found.
                    </p>

                </div>


                <form
                    method="GET"
                    class="search-form"
                >


                    <input
                        type="search"
                        name="search"
                        value="<?= htmlspecialchars($search) ?>"
                        placeholder="Search messages, users or property..."
                    >


                    <button type="submit">

                        <span class="material-symbols-outlined">
                            search
                        </span>

                        Search

                    </button>


                    <?php if ($search !== ''): ?>

                        <a
                            href="admin_messages.php"
                            class="reset-btn"
                        >
                            Reset
                        </a>

                    <?php endif; ?>


                </form>


            </div>



            <!-- =================================================
                 TABLE
            ================================================== -->

            <div class="table-wrap">


                <table>


                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Sender
                            </th>

                            <th>
                                Receiver
                            </th>

                            <th>
                                Property
                            </th>

                            <th>
                                Booking
                            </th>

                            <th>
                                Message
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Sent At
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if ($messageCount > 0): ?>


                        <?php while ($row = mysqli_fetch_assoc($result)): ?>


                            <?php

                            /* Sender name */

                            $senderName = trim(
                                ($row['sender_firstname'] ?? '') .
                                ' ' .
                                ($row['sender_lastname'] ?? '')
                            );

                            if ($senderName === '') {
                                $senderName = 'User #' .
                                    (int)$row['sender_id'];
                            }


                            /* Receiver name */

                            $receiverName = trim(
                                ($row['receiver_firstname'] ?? '') .
                                ' ' .
                                ($row['receiver_lastname'] ?? '')
                            );

                            if ($receiverName === '') {
                                $receiverName = 'User #' .
                                    (int)$row['receiver_id'];
                            }


                            /* Status */

                            $messageStatus =
                                $row['message_status']
                                ?? 'Unknown';

                            $statusClass =
                                strtolower(
                                    preg_replace(
                                        '/[^a-zA-Z0-9]+/',
                                        '-',
                                        $messageStatus
                                    )
                                );

                            ?>


                            <tr>


                                <!-- MESSAGE ID -->

                                <td>

                                    <span class="message-id">

                                        #<?= (int)$row['message_id'] ?>

                                    </span>

                                </td>



                                <!-- SENDER -->

                                <td>

                                    <div class="person">

                                        <div class="person-icon">

                                            <span class="material-symbols-outlined">
                                                person
                                            </span>

                                        </div>


                                        <div>

                                            <strong>
                                                <?= htmlspecialchars(
                                                    $senderName
                                                ) ?>
                                            </strong>

                                            <small>
                                                ID:
                                                <?= (int)$row['sender_id'] ?>
                                            </small>

                                        </div>

                                    </div>

                                </td>



                                <!-- RECEIVER -->

                                <td>

                                    <div class="person">

                                        <div class="person-icon">

                                            <span class="material-symbols-outlined">
                                                person_outline
                                            </span>

                                        </div>


                                        <div>

                                            <strong>
                                                <?= htmlspecialchars(
                                                    $receiverName
                                                ) ?>
                                            </strong>

                                            <small>
                                                ID:
                                                <?= (int)$row['receiver_id'] ?>
                                            </small>

                                        </div>

                                    </div>

                                </td>



                                <!-- PROPERTY -->

                                <td>

                                    <?php if (!empty($row['property_name'])): ?>

                                        <span class="property-name">

                                            <?= htmlspecialchars(
                                                $row['property_name']
                                            ) ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="not-available">
                                            No property
                                        </span>

                                    <?php endif; ?>

                                </td>



                                <!-- BOOKING -->

                                <td>

                                    <?php if (!empty($row['booking_id'])): ?>

                                        <span class="booking-id">

                                            #<?= (int)$row['booking_id'] ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="not-available">
                                            —
                                        </span>

                                    <?php endif; ?>

                                </td>



                                <!-- MESSAGE -->

                                <td class="message-cell">

                                    <?= nl2br(
                                        htmlspecialchars(
                                            $row['message'] ?? ''
                                        )
                                    ) ?>

                                </td>



                                <!-- STATUS -->

                                <td>

                                    <span
                                        class="status <?= htmlspecialchars(
                                            $statusClass
                                        ) ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $messageStatus
                                        ) ?>

                                    </span>

                                </td>



                                <!-- DATE -->

                                <td class="date-cell">

                                    <?= htmlspecialchars(
                                        $row['sent_at']
                                    ) ?>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="8"
                                class="empty"
                            >

                                <div class="empty-icon">

                                    <span class="material-symbols-outlined">
                                        chat
                                    </span>

                                </div>


                                <?php if ($search !== ''): ?>

                                    <h3>
                                        No messages found
                                    </h3>

                                    <p>
                                        Try a different search term.
                                    </p>

                                <?php else: ?>

                                    <h3>
                                        No messages yet
                                    </h3>

                                    <p>
                                        Messages between tenants and landlords
                                        will appear here.

                                    </p>

                                <?php endif; ?>

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </section>



        <!-- =================================================
             INFORMATION NOTE
        ================================================== -->

        <div class="info-note">


            <span class="material-symbols-outlined">
                info
            </span>


            <p>

                Admin can monitor messages for system management.
                Tenants and landlords are responsible for their own communication.

            </p>


        </div>


    </main>


</div>


</body>

</html>