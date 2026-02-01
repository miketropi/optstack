# OptStack – AI Agent Rules (PHP Only)

## Role of the AI Agent

You are an AI Agent contributing to **OptStack**, a PHP-based WordPress Data Stack Framework.

Your primary responsibility is to:
- Implement features safely
- Preserve architectural boundaries
- Ensure all core logic remains testable and stable

Frontend, UI, and JavaScript are **out of scope**.

---

## Absolute Rules (Non-Negotiable)

1. ❌ Do NOT write frontend or UI tests
2. ❌ Do NOT test WordPress core behavior
3. ❌ Do NOT introduce logic into WordPress adapter layers
4. ❌ Do NOT aim for 100% coverage
5. ❌ Do NOT add tests that rely on timing, randomness, or global state

Breaking any rule above is considered a failure.

---

## Architecture Rules

### 1. Core Logic First

- Business rules must live in **pure PHP classes**
- Core logic must be executable **without WordPress loaded**
- WordPress functions are wrappers, not logic holders

If logic requires WordPress to function → refactor.

---

### 2. WordPress Is an Adapter

Allowed WordPress responsibilities:
- Saving meta
- Reading meta
- Registering hooks
- Bootstrapping

Disallowed:
- Validation logic
- Schema decisions
- Conditional resolution
- Data transformation

---

## Testing Rules (PHP)

### Mandatory for Every New Feature

Each new feature MUST include at least one PHPUnit test validating:

- Input → normalized output
- Serialization structure
- Default value behavior

### Mandatory for New Field Types

Every Field type MUST include tests for:

1. normalize()
2. serialize()
3. default value handling

No exceptions.

---

## Searchable Field Rule

If a field supports `searchable()`:

- It MUST store a secondary indexed meta key
- The index format MUST be predictable
- This behavior MUST be covered by a unit test

---

## Allowed Test Types

✅ Unit tests  
✅ Small integration tests (PHP + WP APIs only)

❌ UI tests  
❌ Snapshot tests  
❌ End-to-end browser tests  
❌ Performance tests  

---

## Refactoring Rule

If a test is difficult to write:

1. STOP
2. Refactor the code
3. Simplify responsibilities
4. Then write the test

Never bend tests to fit bad architecture.

---

## Code Change Checklist (Before Submitting)

- [ ] Core logic is WordPress-independent
- [ ] PHPUnit tests added or updated
- [ ] Tests pass locally
- [ ] No UI assumptions added
- [ ] No global state introduced

---

## Definition of Done

A task is complete ONLY when:

- All relevant PHP tests pass
- No architectural rule is violated
- The change improves long-term maintainability

---

## Guiding Principle

> The purpose of the AI Agent is not speed —  
> it is **safe acceleration through structure**.
