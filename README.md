# WP Fix Toolkit

A controlled execution layer for WordPress maintenance and repair actions.

WP Fix Toolkit is designed to turn approved technical operations into explicit, traceable workflows. It separates diagnosis from execution and treats verification as a distinct step.

## Core workflow

1. **Prepare** an action with normalized parameters and a defined scope.
2. **Validate** the proposed operation and its preconditions before approval.
3. **Request approval** from an authorized user for that specific operation.
4. **Revalidate and execute** the approved action once, within its declared scope.
5. **Verify** the resulting site state independently.
6. **Audit** the request, execution, verification, and relevant events.

## Outcome semantics

The coordinator reports the final operation state separately from the fact that the processing stages finished:

- rejected: validation, capability, or approval checks prevented execution.
- succeeded: execution reported success and verification passed.
- failed: execution reported failure or verification failed.
- partial: execution reported a partial result and verification was conclusive without reporting failure.
- unknown: execution or verification could not establish the resulting site state.

A completed PHP method call is not, by itself, evidence that a repair succeeded.

## Design principles

- Explicit approval for each action.
- The host integration validates the proposed operation before requesting approval; the execution coordinator validates again immediately before execution.
- Exact action identity and version resolution.
- Approval bound to a deterministic fingerprint of the complete operation context.
- Single-use approval consumption through an explicit storage contract.
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

The current execution coordinator includes a process-local approval store for tests and same-process integrations only. It is **not durable**, does not coordinate multiple PHP workers, and must not be treated as production-grade replay protection. A production integration must provide an atomic persistent implementation of the approval storage contract.

The project does **not** yet include a WordPress administrative interface, real corrective actions, durable approval storage, or a persistent audit ledger. Those belong to later, separately reviewed stages.

## Execution safety

An approval applies to one exact operation context, including its action identity, requesting user, parameters, execution UUID, and creation timestamp. It expires after 15 minutes and can be consumed only once by a conforming approval store.

The system must not silently retry an interrupted operation. If the outcome cannot be established reliably, it must remain explicitly unknown and require reconciliation.

Audit events are append-only by application policy, not immutable against direct database access. Database transactions can protect the toolkit's own records, but cannot guarantee atomicity with changes made elsewhere in WordPress or by third-party extensions.

## Development status

**Stage:** Execution safety foundations

This project is under active development. The contracts and behavior may evolve before a stable release. No production repair workflow should be assumed available yet.
