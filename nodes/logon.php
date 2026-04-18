<div class="login-shell py-5">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-8 col-lg-5">
				<div class="card shadow-sm border-0 login-card">
					<div class="card-body p-4 p-lg-5">
						<div class="text-center mb-4">
							<div class="login-icon mb-3"><i class="bi bi-calendar2-check"></i></div>
							<h1 class="h3 mb-2">Task Scheduler</h1>
							<p class="text-body-secondary mb-0">Sign in to manage scheduled Zammad tickets.</p>
						</div>

						<?php
						if (isset($_SESSION['logon_error'])) {
							echo "<div class=\"alert alert-danger\" role=\"alert\">";
							echo $_SESSION['logon_error'];
							if (defined('reset_url')) {
								echo " <a href=\"" . reset_url . "\" class=\"alert-link\">Forgot your password?</a>";
							}
							echo "</div>";
						}
						?>

						<form method="post" name="loginSubmit" action="index.php">
							<div class="mb-3">
								<label for="inputUsername" class="form-label">Username</label>
								<input type="text" id="inputUsername" name="inputUsername" class="form-control" placeholder="Username" required autofocus>
							</div>

							<div class="mb-3">
								<label for="inputPassword" class="form-label">Password</label>
								<input type="password" id="inputPassword" name="inputPassword" class="form-control" placeholder="Password" required>
							</div>

							<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
								<div class="form-check mb-0">
									<input class="form-check-input" type="checkbox" value="true" id="remember" name="remember" checked>
									<label class="form-check-label" for="remember">Remember Me</label>
								</div>

								<?php
								if (defined('reset_url')) {
									echo "<a href=\"" . reset_url . "\" class=\"small text-decoration-none\">Forgot your password?</a>";
								}
								?>
							</div>

							<div class="d-grid">
								<button class="btn btn-primary btn-lg" id="loginSubmitButton" type="submit" data-idle-label="Log In" data-loading-label="Signing In...">
									<span class="login-button-spinner spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
									<span class="login-button-label">Log In</span>
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
.login-shell {
	min-height: calc(100vh - 120px);
	display: flex;
	align-items: center;
}

.login-card {
	border-radius: 1.25rem;
	background-color: var(--bs-tertiary-bg);
	border: 1px solid var(--bs-border-color-translucent) !important;
	box-shadow: var(--bs-box-shadow-sm) !important;
}

.login-icon {
	font-size: 2.75rem;
	line-height: 1;
}
</style>
