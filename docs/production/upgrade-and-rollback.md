# Upgrade and rollback

Take a tested database and files backup before updating. Export installed plugin/theme versions and retain the exact previous ZIPs. Rehearse upgrades against a disposable copy. Check the schema marker, existing cart and checkout page content, settings, users and representative orders before reopening traffic.

For a code regression, restore the previous artifact if it can read the migrated data. For incompatible storage changes restore a matching database and files snapshot. Coordinate reconciliation of orders created after the snapshot before undertaking a production restore. Previous-version upgrade and rollback evidence is a separate release gate.
