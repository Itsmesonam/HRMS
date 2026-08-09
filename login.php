<form>

    <h3>Login Here</h3>

    <!-- Username -->
    <label for="username">Username</label>

    <input
        type="text"
        placeholder="Email or phone"
        id="username"
        name="username"
        required
    >


    <!-- Password -->
    <label for="password">Password</label>

    <input
        type="password"
        placeholder="Password"
        id="password"
        name="password"
        required
    >


    <!-- Role -->
    <label for="role">Login As</label>

    <select id="role" name="role" required>

        <option value="" disabled selected>
            Select your role
        </option>

        <option value="admin">
            Admin
        </option>

        <option value="landlord">
            Landlord
        </option>

        <option value="tenant">
            Tenant
        </option>

    </select>


    <!-- Login Button -->
    <button type="submit">
        Log In
    </button>


    <!-- Social Login -->
    <div class="social">

        <div class="fb">
            <i class="fab fa-facebook-f"></i>
            <span>Facebook</span>
        </div>

        <div class="google">
            <i class="fab fa-google"></i>
            <span>Google</span>
        </div>

    </div>


    <!-- Register Link -->
    <div class="register-link">

        Don't have an account?

        <a href="register.php">
            Sign Up
        </a>

    </div>

</form>