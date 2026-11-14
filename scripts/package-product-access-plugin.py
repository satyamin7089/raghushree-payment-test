#!/usr/bin/env python3
"""Build the installable Product Access plugin ZIP reproducibly."""
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo

root = Path(__file__).resolve().parents[1]
source = root / 'integrations' / 'raghushree-product-access' / 'raghushree-product-access.php'
target = root / 'plugin-download' / 'raghushree-product-access.zip'
target.parent.mkdir(parents=True, exist_ok=True)
entry = ZipInfo('raghushree-product-access/raghushree-product-access.php', (2020, 1, 1, 0, 0, 0))
entry.compress_type = ZIP_DEFLATED
entry.external_attr = 0o100644 << 16
with ZipFile(target, 'w', compression=ZIP_DEFLATED) as package:
    package.writestr(entry, source.read_bytes())
print(f'Created {target.relative_to(root)}')
