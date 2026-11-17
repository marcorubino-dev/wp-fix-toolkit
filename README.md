# WP Fix Toolkit

A controlled execution layer for WordPress maintenance and repair actions.

WP Fix Toolkit is designed to turn approved technical operations into explicit, traceable workflows. It separates diagnosis from execution and treats verification as a distinct step.

## Core workflow

1. **Prepare** an action with normalized parameters and a defined scope.
2. **Validate** the action, permissions, parameters, and preconditions.
3. **Request approval** from an authorized user for that specific operation.
4. **Execute** the approved action once, within its declared scope.
5. **Verify** the resulting site state independently.
6. **Audit** the request, execution, verification, and relevant events.

## Design principles

- Explicit approval for each action.
- Validation both before approval and immediately before execution.
- Exact action identity and version resolution.
- Typed validation, execution, and verification results.
- Auditable state transitions and conservative handling of uncertain outcomes.
- Extensibility through stable contracts, without requiring changes to the execution manager.
- No assumption that database transactions can make external WordPress effects atomic.

## Initial scope

The first development stage establishes the foundation:

- PHP 7.4 compatibility.
- Composer autoloading with PSR-4.
- Domain contracts and typed result objects.
- An action registry with duplicate protection and exact version lookup.
- Unit tests, including registration of an external example action.

The initial stage does **not** include an administrative interface, real corrective actions, or database persistence. Those belong to later, separately reviewed stages.

## Execution safety

An approval applies to one prepared operation and expires after 15 minutes. The system must not silently retry an interrupted operation. If the outcome cannot be established reliably, it must remain explicitly unknown and require reconciliation.

Audit events are append-only by application policy, not immutable against direct database access. Database transactions can protect the toolkit's own records, but cannot guarantee atomicity with changes made elsewhere in WordPress or by third-party extensions.

## Development status

**Stage:** Foundation

This project is under active development. The contracts and behavior may evolve before the first stable release. No production repair workflow should be assumed available yet.
