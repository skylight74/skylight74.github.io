#!/usr/bin/env bash
# Full-page screenshots of the three site sections at three widths,
# using headless Firefox with a throwaway profile per shot.
#
# Each profile injects a userContent.css that hides the theme's preloader
# overlay. Firefox 152's --screenshot fires on the load event, before the
# theme JS fades the preloader out, so without this every capture is a blank
# spinner. The section cards carry class="animated active" in the static HTML,
# so hiding the overlay reveals the fully laid-out page immediately.
#
# Usage: capture.sh <base-url> <out-dir> <label>
set -euo pipefail
BASE_URL=$1 OUT_DIR=$2 LABEL=$3
mkdir -p "$OUT_DIR"
OUT_DIR=$(cd "$OUT_DIR" && pwd)   # Firefox --screenshot needs an absolute out path
for W in 1440 768 390; do
  for FRAG in home resume-e contacts-e; do
    URL="$BASE_URL/"
    [ "$FRAG" != "home" ] && URL="$BASE_URL/#$FRAG"
    PROFILE=$(mktemp -d)
    mkdir -p "$PROFILE/chrome"
    # Hide the preloader AND freeze CSS animations/transitions to their end
    # state, so a load-event screenshot captures the settled page rather than a
    # random mid-animation frame (the theme fades/slides cards in via animate.css).
    {
      printf '.preloader,#preloader{display:none!important;opacity:0!important;visibility:hidden!important}\n'
      printf '*,*::before,*::after{animation-duration:0s!important;animation-delay:0s!important;transition-duration:0s!important;transition-delay:0s!important}\n'
    } > "$PROFILE/chrome/userContent.css"
    printf 'user_pref("toolkit.legacyUserProfileCustomizations.stylesheets",true);\n' \
      > "$PROFILE/prefs.js"
    firefox --headless --profile "$PROFILE" --window-size="$W,2400" \
      --screenshot "$OUT_DIR/$LABEL-$FRAG-$W.png" "$URL" >/dev/null 2>&1
    rm -rf "$PROFILE"
  done
done
ls -l "$OUT_DIR"
