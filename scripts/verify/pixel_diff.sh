#!/usr/bin/env bash
# Pixel-compare same-named PNGs across two directories with ImageMagick.
# Prints "<name>: <AE> differing pixels"; writes diff-<name>.png visuals.
# Usage: pixel_diff.sh <dir-a> <dir-b> <out-dir> [fuzz]
set -euo pipefail
A=$1 B=$2 OUT=$3 FUZZ=${4:-2%}
mkdir -p "$OUT"
STATUS=0
for IMG in "$A"/*.png; do
  NAME=$(basename "$IMG")
  # strip the leading label so ref-home-1440.png matches port-home-1440.png
  SUFFIX=${NAME#*-}
  MATCH=$(ls "$B"/*-"$SUFFIX" 2>/dev/null | head -1 || true)
  if [ -z "$MATCH" ]; then
    echo "$SUFFIX: MISSING counterpart in $B"
    STATUS=1
    continue
  fi
  AE=$(compare -metric AE -fuzz "$FUZZ" "$IMG" "$MATCH" \
       "$OUT/diff-$SUFFIX" 2>&1 || true)
  echo "$SUFFIX: $AE differing pixels (fuzz $FUZZ)"
done
exit $STATUS
