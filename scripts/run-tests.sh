#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO_ROOT"

# Digest-pinned official PHP CLI images (resolved 2026-10-08).
PHP72_IMAGE="php@sha256:42ffbc0798e4449bbd1e14fc4dcb87774aa1ad1900a09ef6a965bc0880aa2161"
PHP82_IMAGE="php@sha256:185eb902246c5757dbbb7e439bf92dfea32f627a401b1e2785bee0770fe0c41b"
PHP83_IMAGE="php@sha256:aafe21201943a8a6e497ddfb471e2ce68ada76adede38c7d61fede8918b24319"

DOCKER="${DOCKER:-docker}"
if ! $DOCKER info >/dev/null 2>&1; then
    DOCKER="sudo docker"
fi

run_suite_for_image() {
    local image="$1"
    local label="$2"
    local php_series="$3"
    echo "========== $label ($image) =========="
    $DOCKER run --rm -v "$REPO_ROOT:/app" -w /app "$image" bash /app/scripts/container-test.sh "$php_series"
}

run_suite_for_image "$PHP72_IMAGE" "PHP 7.2.34" "7.2"
run_suite_for_image "$PHP82_IMAGE" "PHP 8.2" "8.2"
run_suite_for_image "$PHP83_IMAGE" "PHP 8.3" "8.3"

echo "All suites passed."
