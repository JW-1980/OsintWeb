# Actor Attribution System - Quick Reference

## Quick Start Guide

### 1. Actor Types at a Glance

| Type | Use When | Common Examples |
|------|----------|-----------------|
| `country` | Nation-states | USA, Russia, China, Israel |
| `terrorist_org` | UN/US/EU designated terrorists | ISIS, Al-Qaeda, Boko Haram |
| `rebel_group` | Armed opposition to government | Free Syrian Army, Myanmar NRF |
| `militia` | Irregular armed forces | Iraqi PMF, Lebanese Hezbollah |
| `paramilitary` | Semi-military organizations | Wagner Group, Academi |
| `private_military` | PMCs and contractors | Blackwater, Executive Outcomes |
| `separatist_group` | Seeking independence | DPR, LPR, Kurdish YPG |
| `insurgent_group` | Armed uprising | Taliban, Houthis |
| `criminal_org` | Organized crime with military ops | Drug cartels (when applicable) |
| `coalition` | Multi-actor alliances | NATO, Coalition Forces |
| `international_org` | Multinational bodies | UN Peacekeepers, OSCE |
| `unknown` | Unidentified | Unknown attackers |

---

## 2. Event Actor Roles

| Role | Description | Example |
|------|-------------|---------|
| `perpetrator` | Carried out the action | Russia in "Russian airstrike" |
| `victim` | Was targeted/affected | Ukraine in "Russian airstrike" |
| `equipment_owner` | Owns the equipment involved | Russia in "Russian tank destroyed" |
| `operator` | Operating equipment | Operator of captured tank |
| `ally` | Allied force participating | US providing air support |
| `mediator` | Diplomatic mediator | Turkey in ceasefire talks |
| `witness` | Witnessing party | UN observers |
| `claimed_by` | Who claimed responsibility | ISIS claiming bombing |
| `attributed_to` | Who analysts attribute it to | (May differ from claimed_by) |
| `other` | Other involvement | Custom role |

---

## 3. Common Event Scenarios

### Scenario 1: Conventional Military Strike
```yaml
Event: Airstrike
Perpetrator: Country A
Victim: Country B
Equipment Owner: Country A (for aircraft used)
Certainty: Confirmed
```

### Scenario 2: Terrorist Attack
```yaml
Event: Suicide Bombing
Perpetrator: Terrorist Organization X
Victim: Country Y
Claimed By: Organization X
Attributed To: Organization X (or possibly different)
Certainty: Confirmed (if claimed) or Likely (if just attributed)
```

### Scenario 3: Equipment Destruction
```yaml
Event: Tank Destroyed
Perpetrator: Country A
Victim: Country B
Equipment Owner: Country B (whose tank was destroyed)
Certainty: Confirmed (with visual evidence)
```

### Scenario 4: Equipment Capture
```yaml
Event: Equipment Captured
Perpetrator: Country A (captured it)
Victim: Country B (lost it)
Equipment Owner: Country B (original owner)
Certainty: Confirmed
```

### Scenario 5: Proxy Warfare
```yaml
Event: Ground Battle
Perpetrator: Militia Group X
Ally: Country A (supporting the militia)
Victim: Rebel Group Y
Ally: Country B (supporting rebels)
Certainty: Likely or Confirmed
```

### Scenario 6: Multi-Actor Combat
```yaml
Event: Battle
Perpetrators:
  - Country A
  - Militia Group X (ally of A)
Victims:
  - Rebel Group Y
  - Militia Group Z (ally of Y)
```

---

## 4. Certainty Levels Guide

| Level | Use When | Examples |
|-------|----------|----------|
| `confirmed` | Visual proof + multiple sources | Video of aircraft with markings |
| `likely` | Strong circumstantial evidence | Compatible with actor's equipment/location |
| `possible` | Some evidence, not conclusive | Reports but no visual proof |
| `alleged` | Claimed but unverified | Actor claims responsibility, no proof |
| `disputed` | Conflicting evidence | Multiple actors claim same action |
| `unconfirmed` | No verification yet | Just reported, awaiting verification |

---

## 5. Database Quick Queries

### Get all events where Russia was the perpetrator
```sql
SELECT e.*
FROM events e
JOIN event_actors ea ON e.id = ea.event_id
JOIN actors a ON ea.actor_id = a.id
WHERE a.iso_code_alpha3 = 'RUS'
  AND ea.role = 'perpetrator';
```

### Get all terrorist organizations involved in events
```sql
SELECT DISTINCT a.*
FROM actors a
JOIN event_actors ea ON a.id = ea.actor_id
WHERE a.actor_type = 'terrorist_org';
```

### Get equipment losses by country
```sql
SELECT
    a.name as country,
    COUNT(*) as losses,
    SUM(CASE WHEN ee.status = 'destroyed' THEN 1 ELSE 0 END) as destroyed,
    SUM(CASE WHEN ee.status = 'captured' THEN 1 ELSE 0 END) as captured
FROM event_equipment ee
JOIN actors a ON ee.equipment_owner_actor_id = a.id
WHERE a.actor_type = 'country'
GROUP BY a.id, a.name;
```

### Find all hostile relationships
```sql
SELECT
    af.name as actor_1,
    at.name as actor_2,
    ar.relationship_type,
    ar.valid_from,
    ar.valid_to
FROM actor_relationships ar
JOIN actors af ON ar.actor_from_id = af.id
JOIN actors at ON ar.actor_to_id = at.id
WHERE ar.relationship_type = 'hostile'
  AND (ar.valid_to IS NULL OR ar.valid_to >= CURDATE());
```

---

## 6. Laravel Eloquent Examples

### Create an actor
```php
$actor = Actor::create([
    'name' => 'Wagner Group',
    'short_name' => 'Wagner',
    'actor_type' => 'private_military',
    'parent_country_id' => $russia->id,
    'also_known_as' => ['Wagner PMC', 'PMC Wagner'],
    'color_hex' => '#8B4513',
    'designation_status' => 'sanctioned',
    'is_active' => false,
]);
```

### Attach actor to event
```php
$event->actors()->attach($russia->id, [
    'role' => 'perpetrator',
    'role_description' => 'Conducted airstrike using Su-34',
    'certainty' => 'confirmed',
    'source_type' => 'visual_evidence',
    'source_url' => 'https://example.com/source',
]);
```

### Get all perpetrators for an event
```php
$perpetrators = $event->actors()
    ->wherePivot('role', 'perpetrator')
    ->get();
```

### Get all events where actor was perpetrator
```php
$events = $actor->events()
    ->wherePivot('role', 'perpetrator')
    ->orderBy('occurred_at', 'desc')
    ->get();
```

### Create relationship between actors
```php
ActorRelationship::create([
    'actor_from_id' => $russia->id,
    'actor_to_id' => $wagner->id,
    'relationship_type' => 'sponsor',
    'valid_from' => '2014-01-01',
    'valid_to' => '2023-06-24',
    'strength' => 'strong',
]);
```

---

## 7. TypeScript/Vue Examples

### Search actors
```typescript
import { useActors } from '@/composables/useActors';

const { actors, searchActors } = useActors();

await searchActors('wagner');
```

### Filter actors by type
```typescript
const countries = actors.value.filter(
  a => a.actor_type === ActorType.COUNTRY
);

const terroristOrgs = actors.value.filter(
  a => a.actor_type === ActorType.TERRORIST_ORG
);
```

### Add actor to event
```typescript
const eventActorData: EventActorForm = {
  actor_id: russia.id,
  role: EventActorRole.PERPETRATOR,
  role_description: 'Conducted airstrike',
  certainty: CertaintyLevel.CONFIRMED,
  source_type: SourceType.VISUAL_EVIDENCE,
  source_url: 'https://example.com/proof',
};

await addActorToEvent(eventId, eventActorData);
```

---

## 8. API Request Examples

### List all actors
```bash
GET /api/actors?actor_type=country,terrorist_org&is_active=true&per_page=50
```

### Search actors
```bash
GET /api/actors/search?q=isis
```

### Get actor details
```bash
GET /api/actors/15
```

### Add actor to event
```bash
POST /api/events/1001/actors
Content-Type: application/json

{
  "actor_id": 1,
  "role": "perpetrator",
  "role_description": "Conducted airstrike",
  "certainty": "confirmed",
  "source_type": "visual_evidence",
  "source_url": "https://example.com/source"
}
```

### Get events by perpetrator
```bash
GET /api/events?perpetrator_id=1&start_date=2024-01-01&end_date=2024-12-31
```

---

## 9. Common Actor Records (Seed Data)

### Major Countries
```
Russia (RU), United States (US), Ukraine (UA), Israel (IL),
Iran (IR), Turkey (TR), Syria (SY), Iraq (IQ), China (CN)
```

### Major Terrorist Organizations
```
ISIS/ISIL/Daesh, Al-Qaeda, Boko Haram, Al-Shabaab,
Taliban, Hamas, Hezbollah (also militia/political)
```

### Major Non-State Armed Groups
```
Free Syrian Army, Wagner Group, YPG/YPJ (Kurdish),
Houthis (Ansar Allah), DPR, LPR, Azov Battalion
```

---

## 10. Visual Badges & Icons

### Actor Type Badges
- Country: 🏳️ + Flag emoji
- Terrorist Org: ⚠️ Red badge
- Rebel Group: 🎯 Orange badge
- Militia: 🛡️ Blue badge
- Paramilitary: 🔫 Brown badge
- Coalition: 🤝 Green badge

### Designation Status Badges
- `terrorist_un`: 🚫 "UN Designated"
- `terrorist_us`: 🚫 "US Designated"
- `sanctioned`: ⛔ "Sanctioned"
- `unrecognized_state`: ⚠️ "Unrecognized"

### Certainty Level Colors
- `confirmed`: ✅ Green
- `likely`: 🟢 Light Green
- `possible`: 🟡 Yellow
- `alleged`: 🟠 Orange
- `disputed`: 🔴 Red
- `unconfirmed`: ⚪ Gray

---

## 11. Common Pitfalls & Best Practices

### ✅ DO:
- Always specify `certainty` level for attributions
- Provide `source_url` whenever possible
- Use `equipment_owner` role for equipment events
- Set `parent_country_id` for non-state actors linked to countries
- Use `also_known_as` for aliases and alternate names
- Mark inactive actors with `is_active = false`

### ❌ DON'T:
- Don't use `perpetrator` role for victims
- Don't mix `claimed_by` and `attributed_to` (they can differ)
- Don't leave `certainty` as 'unconfirmed' indefinitely
- Don't create duplicate actors (search first)
- Don't assign equipment to actors with wrong type
- Don't forget to add sources for verification

---

## 12. Validation Rules

### Actor Creation
```
name: required, max:255
actor_type: required, enum
iso_code_alpha2: required if actor_type = 'country', char:2
color_hex: nullable, regex:/^#[0-9A-F]{6}$/i
founded_date: nullable, date, before_or_equal:today
dissolved_date: nullable, date, after:founded_date
parent_country_id: nullable, exists:actors,id where actor_type='country'
```

### Event Actor Assignment
```
actor_id: required, exists:actors,id
role: required, enum
certainty: required, enum
personnel_count: nullable, integer, min:0
source_url: nullable, url, max:1000
```

---

## 13. Performance Tips

### Indexes
```sql
-- Already included in schema, but verify:
CREATE INDEX idx_actor_type ON actors(actor_type);
CREATE INDEX idx_event_role ON event_actors(event_id, role);
CREATE INDEX idx_active_actors ON actors(is_active, actor_type);
```

### Eager Loading
```php
// Load actors with events
$actors = Actor::with(['events', 'aliases', 'parentCountry'])->get();

// Load events with actors
$events = Event::with(['actors.actor'])->get();
```

### Caching
```php
// Cache popular actors
Cache::remember('countries', 3600, function () {
    return Actor::where('actor_type', 'country')->get();
});
```

---

## 14. Testing Checklist

- [ ] Can create all actor types
- [ ] Can assign actors to events with all roles
- [ ] Can filter events by actor
- [ ] Can filter events by role
- [ ] Certainty levels work correctly
- [ ] Equipment ownership tracked properly
- [ ] Multi-actor events work
- [ ] Actor relationships save and load
- [ ] Actor search works with aliases
- [ ] Inactive actors don't appear in default listings
- [ ] Validation prevents invalid data
- [ ] API endpoints return correct data
- [ ] Frontend displays actors correctly

---

This quick reference should help developers implement and use the Actor Attribution System efficiently. For complete details, see the full specification document.
