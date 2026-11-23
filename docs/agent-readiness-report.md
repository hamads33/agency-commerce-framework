# Agent readiness

`AGENTS.md` maps tasks to owners and links the [agent workflow](development/agent-workflow.md). Backend fixtures use WordPress and WooCommerce APIs. CI separates fast checks from isolated backend runs and retains per-run logs. `npm run package` constructs and verifies staged ZIPs from tracked runtime files.

Use the [client customization model](architecture/store-customization.md) before copying a theme. Use the [component contracts](architecture/component-contracts.md) and [plugin boundary](architecture/plugin-theme-boundary.md) when changing markup or block metadata. Escalate evidence gaps through the [release gates](production/release-gates.md). CI configuration is present. Passing status must come from completed runs.
