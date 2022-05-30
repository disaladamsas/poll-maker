<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand mr-4" href="/">Heisenberge Polls</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto" style="width: 100%">
                <li class="nav-item active">
                    <a class="nav-link" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/discover">Discover</a>
                </li>
                <?php if (!isset($_SESSION["username"])) : ?>
                    <li class="nav-item sign-up-btn">
                        <a class="nav-link" href="/auth/login">Sign In</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/auth/register">Register</a>
                    </li>
                <?php else : ?>
                    <li class="dropdown">
                        <a class="dropdown-toggle" href="" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo $_SESSION["username"] ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li><a class="dropdown-item" href="/settings">Settings</a></li>
                            <li><a class="dropdown-item" href="/auth/logout">Log Out</a></li>
                            <li><a class="dropdown-item" href="/auth/delete-account">Delete My Account</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>