<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Protect page
requireAuth('../auth/login.php');
requireRole('ADMIN');

$error = '';
$success = '';
$sentCount = 0;

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
				$error = 'Invalid CSRF token.';
		} else {
				$subject = sanitizeInput($_POST['subject'] ?? '');
				$messageRaw = trim($_POST['message'] ?? '');
				$recipients = $_POST['recipients'] ?? 'subscribers'; // 'subscribers' or 'all'

				if (empty($subject) || empty($messageRaw)) {
						$error = 'Subject and message are required.';
				} else {
						// Build recipients query
						$sql = "SELECT u.id, u.email, p.first_name
										FROM users u
										LEFT JOIN user_profiles p ON u.id = p.user_id
										WHERE u.is_active = 1";
						if ($recipients === 'subscribers') {
								$sql .= " AND p.newsletter = 1";
						}

						$users = fetchAll($sql);

						foreach ($users as $user) {
								$to = $user['email'];
								$name = $user['first_name'] ?? '';
								$personalized = "<p>Hi " . htmlspecialchars($name) . ",</p>\n" . nl2br(htmlspecialchars($messageRaw));
								$body = "
										<html><body>
										" . $personalized . "
										<hr>
										<p style=\"font-size:12px;color:#777;\">You are receiving this email from Sahara.</p>
										</body></html>
								";
								$headers = "MIME-Version: 1.0\r\n";
								$headers .= "Content-type: text/html; charset=UTF-8\r\n";
								$headers .= "From: Sahara <noreply@sahara.com>\r\n";

								// In development: log the email instead of sending
								if (in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1'])) {
										error_log("Newsletter -> To: {$to} | Subject: {$subject}\nBody: " . strip_tags($body));
										$sentCount++;
								} else {
										if (@mail($to, $subject, $body, $headers)) {
												$sentCount++;
										} else {
												error_log("Failed to send newsletter to: {$to}");
										}
								}
						}

						$success = "Newsletter queued/sent to {$sentCount} recipient(s).";
				}
		}
}

// Fetch subscriber counts for UI
$totalRow = fetchOne("SELECT COUNT(*) as c FROM users");
$totalUsers = $totalRow ? intval($totalRow['c']) : 0;
$newsRow = fetchOne(
		"SELECT COUNT(*) as c
		 FROM users u
		 LEFT JOIN user_profiles p ON u.id = p.user_id
		 WHERE u.is_active = 1 AND p.newsletter = 1"
);
$newsletterUsers = $newsRow ? intval($newsRow['c']) : 0;

$csrf = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>Sahara Admin - Newsletter</title>
	<link rel="stylesheet" href="/css/main.css" />
	<link rel="stylesheet" href="/css/auth.css" />
	<style>
		.admin-card { max-width:900px; margin:32px auto; background:var(--mantle); padding:24px; border-radius:12px; border:1px solid var(--surface0); }
		.field-row { display:flex; gap:12px; align-items:center; margin-bottom:12px; }
		.field-row .meta { color:var(--subtext0); font-size:13px; }
		.preview-box { background:var(--base); padding:16px; border-radius:8px; border:1px solid var(--surface0); color:var(--text); max-height:360px; overflow:auto; }
	</style>
</head>
<body>
	<?php include __DIR__ . '/../partials/header.php'; ?>

	<main>
		<div class="admin-card">
			<h2>Send Newsletter</h2>

			<?php if ($error): ?>
				<div class="alert alert-error" style="margin-bottom:12px;">
					<span class="material-symbols-outlined">error</span>
					<span><?php echo htmlspecialchars($error); ?></span>
				</div>
			<?php endif; ?>

			<?php if ($success): ?>
				<div class="alert alert-success" style="margin-bottom:12px;">
					<span class="material-symbols-outlined">check_circle</span>
					<span><?php echo htmlspecialchars($success); ?></span>
				</div>
			<?php endif; ?>

			<form method="post" id="newsletter-form" novalidate>
				<input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>" />

				<div class="form-group">
					<label for="subject">Subject</label>
					<input id="subject" name="subject" type="text" placeholder="Newsletter subject" required />
				</div>

				<div class="form-group">
					<label for="message">Message (plain text allowed; basic formatting preserved)</label>
					<textarea id="message" name="message" rows="10" placeholder="Write your newsletter..." required></textarea>
				</div>

				<div class="field-row">
					<div>
						<label><input type="radio" name="recipients" value="subscribers" checked /> Subscribers (<?php echo intval($newsletterUsers); ?>)</label>
					</div>
					<div>
						<label><input type="radio" name="recipients" value="all" /> All Active Users (<?php echo intval($totalUsers); ?>)</label>
					</div>
					<div class="meta" style="margin-left:auto;">Tip: For development, messages are logged to error_log on localhost.</div>
				</div>

				<div style="display:flex;gap:12px;margin-top:16px;">
					<button type="button" id="preview-btn" class="btn-primary">Preview</button>
					<button type="submit" class="btn-primary" style="background:var(--green)">Send Newsletter</button>
				</div>
			</form>

			<div id="preview-modal" style="display:none; margin-top:18px;">
				<h3>Preview</h3>
				<div class="preview-box" id="preview-content"></div>
				<div style="margin-top:12px;">
					<button id="close-preview" class="btn-primary">Close Preview</button>
				</div>
			</div>
		</div>
	</main>

	<script>
		(function(){
			const previewBtn = document.getElementById('preview-btn');
			const closeBtn = document.getElementById('close-preview');
			const modal = document.getElementById('preview-modal');
			const previewContent = document.getElementById('preview-content');

			previewBtn.addEventListener('click', () => {
				const subject = document.getElementById('subject').value.trim();
				const message = document.getElementById('message').value.trim();
				if (!subject && !message) return alert('Add subject or message to preview.');
				const esc = (s) => s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
				previewContent.innerHTML = '<strong>' + esc(subject) + '</strong><hr>' + esc(message).replace(/\n/g,'<br>');
				modal.style.display = 'block';
				modal.scrollIntoView({behavior:'smooth'});
			});

			closeBtn && closeBtn.addEventListener('click', () => {
				modal.style.display = 'none';
			});
		})();
	</script>
</body>
</html>
