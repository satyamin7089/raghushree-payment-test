#!/usr/bin/env python3
"""Build the small, installable WordPress plugin ZIP from its source directory."""
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo

root = Path(__file__).resolve().parents[1]
source = root / 'integrations' / 'raghushree-payments'
target = root / 'plugin-download' / 'raghushree-payments.zip'
target.parent.mkdir(parents=True, exist_ok=True)
with ZipFile(target, 'w', compression=ZIP_DEFLATED) as package:
    for name in ('checkout.js', 'raghushree-payments.php'):
        entry = ZipInfo(f'raghushree-payments/{name}', (2020, 1, 1, 0, 0, 0))
        entry.compress_type = ZIP_DEFLATED
        entry.external_attr = 0o100644 << 16
        package.writestr(entry, (source / name).read_bytes())
print(f'Created {target.relative_to(root)}')
