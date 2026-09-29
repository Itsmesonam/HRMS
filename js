/* Name Validation */
document.querySelectorAll(".name-only").forEach(function (input) {
    input.addEventListener("input", function () {
        this.value = this.value.replace(/[^a-zA-Z ]/g, "");
    });
});

/* Number Validation */
document.querySelectorAll(".number-only").forEach(function (input) {
    input.addEventListener("input", function () {
        this.value = this.value.replace(/[^0-9]/g, "");
    });
});

/* Email Validation */
document.querySelectorAll(".email-field").forEach(function (input) {
    input.addEventListener("input", function () {
        this.setCustomValidity("");

        if (this.value && !this.validity.valid) {
            this.setCustomValidity("Please enter a valid email address.");
        }
    });
});

/* Password Length Validation */
document.querySelectorAll(".password-field").forEach(function (input) {
    input.addEventListener("input", function () {
        if (this.value.length < 6) {
            this.setCustomValidity("Password must contain at least 6 characters.");
        } else {
            this.setCustomValidity("");
        }
    });
});