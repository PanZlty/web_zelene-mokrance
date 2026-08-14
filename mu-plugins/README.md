# MU-plugin contract

Top-level PHP files in this directory are loaded automatically by WordPress. Subdirectories require a loader file.

Rules:
- Every PHP file starts with an ABSPATH guard.
- Hooks are idempotent and scoped to the required context.
- No credentials or environment-specific paths are hardcoded.
- Parcel data belongs in WordPress-native content or options, not hidden PHP arrays.
- Production deployment is performed only by the GitHub Actions workflow.
- SFTP target is configured in GitHub Secrets and is not stored here.

## Active module

`zelene-mokrance-frontend-hardening.php` is the first reviewed technical-hardening module. It:

- hides the Houzez compare panel and login/register/reset demo UI on the public frontend;
- replaces the old `http://zelenemokrance.sk` root menu URL with the configured canonical `home_url()`;
- carries reviewed published-page layout rules out of YellowPencil into version-controlled code;
- removes YellowPencil's generated frontend CSS while keeping YellowPencil available in wp-admin/live preview;
- intentionally does not migrate draft-only Byty newsletter styling or delete YellowPencil data, so rollback remains possible.

The module is safe to roll back by removing the file from the deployed MU-plugin directory. YellowPencil options and post meta are not deleted by this change.

## Deployment status

The code is committed to GitHub. The GitHub Actions workflow runs on push to `main` (PHP syntax check passes), but the SFTP upload step fails: `SFTP_*` secrets or `SFTP_TARGET` need to be verified with the hosting administrator before production deploy works. Until then, changed MU-plugin files are also placed on the server manually and kept in sync with the repo.

## Footer year module

`zelene-mokrance-footer.php` registers the `zm_footer_year` action used by the Bricks dynamic tag `{do_action:zm_footer_year}` in the footer copyright text, so the year updates automatically.
