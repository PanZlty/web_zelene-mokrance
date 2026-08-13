# MU-plugin contract

Top-level PHP files in this directory are loaded automatically by WordPress. Subdirectories require a loader file.

Rules:
- Every PHP file starts with an ABSPATH guard.
- Hooks are idempotent and scoped to the required context.
- No credentials or environment-specific paths are hardcoded.
- Parcel data belongs in WordPress-native content or options, not hidden PHP arrays.
- Production deployment is performed only by the GitHub Actions workflow.
- SFTP target is configured in GitHub Secrets and is not stored here.

No production MU-plugin is included yet. The first code change should be the reviewed P0 technical-hardening module after the redirect/menu and Elementor content decisions are approved.