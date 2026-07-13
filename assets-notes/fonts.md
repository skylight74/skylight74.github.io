# Font Assets: IBM Plex Subsets

## Source Files

All fonts sourced from system TTF files:

- `/usr/share/fonts/TTF/IBMPlexMono-Regular.ttf` → `plex-mono-400.woff2`
- `/usr/share/fonts/TTF/IBMPlexMono-Medium.ttf` → `plex-mono-500.woff2`
- `/usr/share/fonts/TTF/IBMPlexMono-SemiBold.ttf` → `plex-mono-600.woff2`
- `/usr/share/fonts/TTF/IBMPlexSans-Regular.ttf` → `plex-sans-400.woff2`
- `/usr/share/fonts/TTF/IBMPlexSans-SemiBold.ttf` → `plex-sans-600.woff2`
- `/usr/share/fonts/TTF/IBMPlexSerif-SemiBold.ttf` → `plex-serif-600.woff2`
- `/usr/share/fonts/TTF/IBMPlexSerif-MediumItalic.ttf` → `plex-serif-500i.woff2`

## Subset Command

```bash
python3 -m fontTools.subset <input.ttf> --output-file=static/fonts/<name>.woff2 --flavor=woff2 \
  --unicodes="U+0000-00FF,U+0100-017F,U+0180-024F,U+2013,U+2014,U+2018-201F,U+2022,U+2026,U+00D7,U+2192,U+2190,U+25CF,U+00B7" \
  --layout-features="kern,liga" --no-hinting
```

### Unicode Ranges

- `U+0000-00FF`: Basic Latin + Latin-1 Supplement
- `U+0100-017F`: Latin Extended-A
- `U+0180-024F`: Latin Extended-B (includes Turkish: İ, ş, ğ, ç, ö, ü)
- `U+2013, U+2014`: En-dash, Em-dash
- `U+2018-201F`: Quotation marks
- `U+2022`: Bullet
- `U+2026`: Ellipsis
- `U+00D7`: Multiply sign
- `U+2192, U+2190`: Arrows
- `U+25CF`: Filled circle
- `U+00B7`: Middle dot

### Layout Features

- `kern`: Kerning
- `liga`: Ligatures

### Hinting

Hinting removed (`--no-hinting`) for smaller file size.

## File Sizes

```
plex-mono-400.woff2      10.1K
plex-mono-500.woff2      10.3K
plex-mono-600.woff2      10.4K
plex-sans-400.woff2      14.0K
plex-sans-600.woff2      15.0K
plex-serif-600.woff2     15.7K
plex-serif-500i.woff2    17.4K
─────────────────────────────
Total                   104.0K (under 120KB budget)
```

## License

IBM Plex fonts are licensed under the SIL Open Font License (OFL), Version 1.1.

See: https://github.com/IBM/plex/blob/main/LICENSE.txt

## Date

Generated: 2026-07-12
