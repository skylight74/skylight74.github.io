#!/usr/bin/env bash
# Full-page screenshots of the three site sections at three widths,
# using headless Firefox with a throwaway profile per shot.
# Usage: capture.sh <base-url> <out-dir> <label>
set -euo pipefail
BASE_URL=$1 OUT_DIR=$2 LABEL=$3
mkdir -p "$OUT_DIR"
for W in 1440 768 390; do
  for FRAG in home resume-e contacts-e; do
    URL="$BASE_URL/"
    [ "$FRAG" != "home" ] && URL="$BASE_URL/#$FRAG"
    PROFILE=$(mktemp -d)
    firefox --headless --profile "$PROFILE" --window-size="$W,2400" \
      --screenshot "$OUT_DIR/$LABEL-$FRAG-$W.png" "$URL" >/dev/null 2>&1
    rm -rf "$PROFILE"
  done
done
ls -l "$OUT_DIR"
