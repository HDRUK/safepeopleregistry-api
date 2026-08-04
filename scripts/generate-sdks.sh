#!/usr/bin/env bash
#
# Regenerates the OpenAPI spec from @OA annotations and builds the Python, C#,
# Java, Go, Rust, and TypeScript client SDKs from it. Output goes to
# sdks/<language> (git-ignored) — this script does not publish anything.
#
# Usage: scripts/generate-sdks.sh [--skip-validate] [--version <semver>]

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

SKIP_VALIDATE=""
VERSION="0.0.0-local"

while [[ $# -gt 0 ]]; do
  case "$1" in
    --skip-validate)
      SKIP_VALIDATE="--skip-validate-spec"
      shift
      ;;
    --version)
      VERSION="$2"
      shift 2
      ;;
    *)
      echo "Unknown argument: $1" >&2
      exit 1
      ;;
  esac
done

# Strip a leading "v" (e.g. a git tag like v1.2.3) — PyPI/NuGet want plain semver.
PACKAGE_VERSION="${VERSION#v}"

echo "==> Regenerating OpenAPI spec (php artisan l5-swagger:generate)"
php artisan l5-swagger:generate

echo "==> Stripping internal-only endpoints (x-internal) from the spec"
php artisan app:strip-internal-endpoints

SPEC="storage/api-docs/api-docs.json"
OUT_DIR="sdks"
rm -rf "$OUT_DIR"
mkdir -p "$OUT_DIR"

echo "==> Generating Python SDK (version $PACKAGE_VERSION)"
npx --yes @openapitools/openapi-generator-cli generate \
  -i "$SPEC" \
  -g python \
  -o "$OUT_DIR/python" \
  --git-user-id HDRUK \
  --git-repo-id safepeopleregistry-api-python-sdk \
  --package-name safepeopleregistry_api_sdk \
  --additional-properties=packageVersion="$PACKAGE_VERSION" \
  $SKIP_VALIDATE

echo "==> Generating C# SDK (version $PACKAGE_VERSION)"
npx --yes @openapitools/openapi-generator-cli generate \
  -i "$SPEC" \
  -g csharp \
  -o "$OUT_DIR/csharp" \
  --git-user-id HDRUK \
  --git-repo-id safepeopleregistry-api-csharp-sdk \
  --additional-properties=packageName=SafePeopleRegistryApiSdk,packageVersion="$PACKAGE_VERSION" \
  $SKIP_VALIDATE

echo "==> Generating Java SDK (version $PACKAGE_VERSION)"
npx --yes @openapitools/openapi-generator-cli generate \
  -i "$SPEC" \
  -g java \
  -o "$OUT_DIR/java" \
  --git-user-id HDRUK \
  --git-repo-id safepeopleregistry-api-java-sdk \
  --additional-properties=groupId=uk.ac.hdruk.safepeopleregistryapi,artifactId=safepeopleregistry-api-sdk,invokerPackage=uk.ac.hdruk.safepeopleregistryapi,apiPackage=uk.ac.hdruk.safepeopleregistryapi.api,modelPackage=uk.ac.hdruk.safepeopleregistryapi.model,artifactVersion="$PACKAGE_VERSION" \
  $SKIP_VALIDATE

echo "==> Generating Go SDK (version $PACKAGE_VERSION)"
npx --yes @openapitools/openapi-generator-cli generate \
  -i "$SPEC" \
  -g go \
  -o "$OUT_DIR/go" \
  --git-user-id HDRUK \
  --git-repo-id safepeopleregistry-api-go-sdk \
  --additional-properties=packageName=safepeopleregistrysdk,packageVersion="$PACKAGE_VERSION" \
  $SKIP_VALIDATE

echo "==> Generating Rust SDK (version $PACKAGE_VERSION)"
npx --yes @openapitools/openapi-generator-cli generate \
  -i "$SPEC" \
  -g rust \
  -o "$OUT_DIR/rust" \
  --git-user-id HDRUK \
  --git-repo-id safepeopleregistry-api-rust-sdk \
  --additional-properties=packageName=safepeopleregistry-api-sdk,packageVersion="$PACKAGE_VERSION" \
  $SKIP_VALIDATE

echo "==> Generating TypeScript SDK (version $PACKAGE_VERSION)"
npx --yes @openapitools/openapi-generator-cli generate \
  -i "$SPEC" \
  -g typescript-axios \
  -o "$OUT_DIR/typescript" \
  --git-user-id HDRUK \
  --git-repo-id safepeopleregistry-api-typescript-sdk \
  --additional-properties=npmName=@hdruk/safepeopleregistry-api-sdk,npmVersion="$PACKAGE_VERSION" \
  $SKIP_VALIDATE

echo "==> Copying Custodian request-signing helpers into each SDK"
HELPERS_DIR="sdk-helpers"

cp "$HELPERS_DIR/python/custodian_signing.py" "$OUT_DIR/python/safepeopleregistry_api_sdk/"
cp "$HELPERS_DIR/typescript/custodianSigning.ts" "$OUT_DIR/typescript/"
cp "$HELPERS_DIR/csharp/CustodianSigning.cs" "$OUT_DIR/csharp/src/SafePeopleRegistryApiSdk/"
cp "$HELPERS_DIR/java/CustodianSigning.java" "$OUT_DIR/java/src/main/java/uk/ac/hdruk/safepeopleregistryapi/"
cp "$HELPERS_DIR/go/custodian_signing.go" "$OUT_DIR/go/"
cp "$HELPERS_DIR/rust/custodian_signing.rs" "$OUT_DIR/rust/src/"

# The Rust helper needs hmac/sha2/base64, which the generated Cargo.toml
# doesn't include by default - insert them into the [dependencies] table
# (not just appended to EOF, which would land after [features] instead).
if ! grep -q '^hmac ' "$OUT_DIR/rust/Cargo.toml"; then
  awk '
    /^\[dependencies\]/ { print; print "hmac = \"^0.12\""; print "sha2 = \"^0.10\""; print "base64 = \"^0.21\""; next }
    { print }
  ' "$OUT_DIR/rust/Cargo.toml" > "$OUT_DIR/rust/Cargo.toml.tmp"
  mv "$OUT_DIR/rust/Cargo.toml.tmp" "$OUT_DIR/rust/Cargo.toml"
fi
# Wire the new module into the Rust crate root so it's actually compiled.
if ! grep -q "custodian_signing" "$OUT_DIR/rust/src/lib.rs"; then
  echo "pub mod custodian_signing;" >> "$OUT_DIR/rust/src/lib.rs"
fi

echo "==> Done. SDKs written to $OUT_DIR/{python,csharp,java,go,rust,typescript}"
