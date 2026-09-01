/* =========================
   RESET
========================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


/* =========================
   BODY
========================= */

body {

    min-height: 100vh;

    font-family: Arial, sans-serif;

    background: linear-gradient(
        135deg,
        #e8f5e9,
        #c8e6c9
    );

}


/* =========================
   NAVIGATION
========================= */

nav {

    width: 100%;

    height: 65px;

    background: #1b5e20;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 40px;

}


.logo {

    color: white;

    font-size: 25px;

    font-weight: bold;

}


.nav-links {

    display: flex;

    gap: 25px;

}


.nav-links a {

    color: white;

    text-decoration: none;

    font-size: 15px;

    font-weight: bold;

}


.nav-links a:hover {

    text-decoration: underline;

}


/* =========================
   BOOKING CONTAINER
========================= */

.booking-container {

    width: 500px;

    max-width: 95%;

    margin: 50px auto;

    padding: 35px;

    background: white;

    border-radius: 12px;

    box-shadow:
        0 10px 30px rgba(0, 0, 0, 0.15);

}


/* =========================
   TITLE
========================= */

.booking-container h1 {

    text-align: center;

    color: #1b5e20;

    margin-bottom: 10px;

}


.welcome {

    text-align: center;

    color: #666;

    margin-bottom: 25px;

}


/* =========================
   LABEL
========================= */

.booking-container label {

    display: block;

    margin-top: 15px;

    margin-bottom: 6px;

    font-weight: bold;

    color: #333;

}


/* --INPUT / SELECT / TEXTAREA-- */

.booking-container input,
.booking-container select,
.booking-container textarea {

    width: 100%;

    padding: 12px;

    border: 1px solid #b8ccb9;

    border-radius: 6px;

    background: #f5faf6;

    font-size: 15px;

    font-family: Arial, sans-serif;

    outline: none;

}


.booking-container input,
.booking-container select {

    height: 45px;

}


.booking-container textarea {

    resize: vertical;

}


.booking-container input:focus,
.booking-container select:focus,
.booking-container textarea:focus {

    border-color: #4caf50;

    background: white;

}


/* --BUTTON-- */

.booking-container button {

    width: 100%;

    height: 45px;

    margin-top: 25px;

    border: none;

    border-radius: 6px;

    background: #2e7d32;

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

}


.booking-container button:hover {

    background: #1b5e20;

}


/* --BACK BUTTON-- */

.back-btn {

    display: block;

    text-align: center;

    margin-top: 20px;

    color: #2e7d32;

    text-decoration: none;

    font-weight: bold;

}


.back-btn:hover {

    text-decoration: underline;

}


/* --MOBILE--*/

@media (max-width: 600px) {

    nav {

        padding: 0 15px;

    }

    .nav-links {

        gap: 10px;

    }

    .nav-links a {

        font-size: 12px;

    }

    .booking-container {

        padding: 25px 20px;

    }

}