# UI Journey Test — [Path Title]

**Date:** YYYY-MM-DD  
**Tester:** (agent / human)  
**Base URL:** http://localhost:8080  
**Overall result:** PASS | FAIL

---

## Path Description

> Short description of the user journey being tested.  
> Example: "New player opens the game, creates a character, enters the dungeon, survives one combat, and extracts."

---

## Steps

| # | Action | Selector / URL | Expected | Result | Screenshot |
|---|--------|---------------|----------|--------|------------|
| 1 | goto | `/` | Home screen visible | PASS | [step-01](./screenshots/SLUG/step-01.png) |
| 2 | click | `#start-game` | Character creation shown | PASS | [step-02](./screenshots/SLUG/step-02.png) |
| … | … | … | … | … | … |

---

## Failures

_List any failed steps with error detail. Leave blank if all passed._

### Step N — [action]

- **Selector / URL:** `…`
- **Expected:** …
- **Actual error:** `…`
- **Screenshot:** [step-N-FAIL](./screenshots/SLUG/step-N-FAIL.png)

---

## Observations

> Free-form notes about unexpected behaviour, visual glitches, or UX concerns noticed during the run.

---

## Raw Script Output

```json
{
  "baseUrl": "http://localhost:8080",
  "passed": true,
  "steps": []
}
```

---

## Recommended Follow-up

- [ ] …
