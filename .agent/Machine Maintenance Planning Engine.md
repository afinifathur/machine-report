# Machine Maintenance Planning Engine
## Architecture & Functional Design Proposal

> **Document Type:** Product Requirements Document + System Architecture Design
> **Prepared for:** Machine Report — Laravel CMMS Project
> **System Maturity at Time of Writing:** ~60%
> **Author Role:** Senior CMMS Architect / Maintenance Planning Expert / Laravel System Architect
> **Date:** 2026-07-28

---

## Table of Contents

1. [Current Architecture Analysis](#1-current-architecture-analysis)
2. [Gap Analysis](#2-gap-analysis)
3. [Planning Engine Concept](#3-planning-engine-concept)
4. [Machine Health Engine](#4-machine-health-engine)
5. [Checklist Engine](#5-checklist-engine)
6. [Dynamic Scheduling Engine](#6-dynamic-scheduling-engine)
7. [Technician Workload Engine](#7-technician-workload-engine)
8. [Calendar UX Design](#8-calendar-ux-design)
9. [Recommended Database Model](#9-recommended-database-model)
10. [Roadmap](#10-roadmap)
11. [Risk Analysis](#11-risk-analysis)
12. [Final Recommendation](#12-final-recommendation)

---

## 1. Current Architecture Analysis

### 1.1 Architecture Overview

The current system follows a **classic Laravel MVC pattern** augmented with a lightweight Service + Repository layer. The directory structure:

```
app/
├── Enums/             ← ProcurementStatus, ProcurementUrgency, ApprovalDecision
├── Http/Controllers/  ← 12 controllers covering all features
├── Integrations/      ← (directory exists, currently empty)
├── Models/            ← 21 Eloquent models
├── Policies/          ← Authorization (Spatie Permissions active)
├── Providers/         ← AppServiceProvider
├── Repositories/      ← WarehouseRepository (Interface + Mock + Adapter)
└── Services/          ← ImageCompression, QrCode, MaintenanceReadiness, ProcurementWorkflow
```

**The architecture is clean and follows good principles:**
- Repository pattern is correctly applied for warehouse integration
- Service layer exists for business logic separation
- Eloquent relationships are well-defined
- Spatie permissions scaffolding is in place
- Soft deletes applied to critical models (ProcurementCase)

---

### 1.2 Existing Module Inventory

| Module | Status | Maturity |
|---|---|---|
| Machine Passport (Code, Name, Dept, Location, Category, Criticality) | ✅ Done | 90% |
| Machine Photos (Gallery, Reference, Rotation) | ✅ Done | 85% |
| Machine Documents / Manual Book (Links + Legacy Upload) | ✅ Done | 85% |
| Machine Components | ✅ Done | 70% |
| Machine QR Code (Generate, Print, Download) | ✅ Done | 90% |
| Machine Production Area (Master Table + FK) | ✅ Done | 80% |
| Machine Criticality Field | ✅ Done | 70% (field exists, no criticality matrix) |
| Sparepart Mapping (machine → warehouse item code) | ✅ Done | 80% |
| Critical Sparepart Monitor (with lead time, criticality flag) | ✅ Done | 75% |
| Warehouse Integration (Interface + Mock, ready for real WMS) | ✅ Done | 70% |
| Special Procurement (full workflow, approval, PO, delivery) | ✅ Done | 85% |
| Maintenance Template (SOP Package with checklists & spareparts) | ✅ Done | 65% |
| Maintenance Plan (1 plan = 1 machine + 1 template + date) | ✅ Done | 60% |
| Maintenance Execution (QR scan → checklist → photo → score) | ✅ Done | 55% |
| Readiness Audit (6-factor check: machine/template/checklist/parts/doc/tech) | ✅ Done | 70% |
| Morning Briefing Dashboard (Agenda, Blockers, Priority, Timeline) | ✅ Done | 65% |
| Role-Based Access Control (Spatie Permissions scaffolded) | ⚠️ Partial | 40% |
| Maintenance Planning Calendar | ❌ Missing | 0% |
| Technician Management | ❌ Missing | 0% |
| Working Calendar / Holiday Engine | ❌ Missing | 0% |
| Machine Health Score Engine (real, not simulated) | ❌ Missing | 5% (hardcoded mock) |
| Auto-Scheduling / PM Frequency Engine | ❌ Missing | 0% |
| Breakdown / Emergency Work Order | ❌ Missing | 0% |
| Dynamic Rescheduling Engine | ❌ Missing | 0% |
| Technician Workload Balancing | ❌ Missing | 0% |
| Reporting & Analytics | ❌ Missing | 0% |

---

### 1.3 Current Strengths

1. **Solid foundation.** The Machine Passport + Sparepart mapping + Warehouse integration give the planning engine real data to work with.

2. **The readiness audit service is a gem.** `MaintenanceReadinessService::getReadinessReport()` is architecturally correct — it checks 6 blockers before any plan can be executed. This concept must be extended, not replaced.

3. **The QR → Execution flow exists.** Technicians can scan a QR code, open today's plan, and complete the checklist on mobile. This is 70% of what a field CMMS needs.

4. **Repository pattern is correctly applied.** The `WarehouseRepositoryInterface` + `MockWarehouseRepository` means swapping to a real WMS connection requires no changes to the service layer. This is excellent architecture.

5. **The `generation_source` field in maintenance_plans** (`Manual`, `Generated`, `Imported`) shows forward-thinking — the team anticipated automated plan generation.

6. **Procurement workflow is mature.** The multi-stage approval chain (Admin Maintenance → Kabag → Direktur → Purchasing → Delivery) is a sophisticated and complete workflow.

7. **Spatie Permissions is installed.** Role-based access control infrastructure exists even if roles are not fully defined yet.

---

### 1.4 Current Weaknesses

1. **Health score is hardcoded.** `Machine::getHealthScoreAttribute()` returns static values mapped to hardcoded machine codes. This is a placeholder and must be replaced with a real calculation engine fed by execution history.

2. **Technician is a plain string.** `maintenance_plans.assigned_technician` is a `varchar` field, not a foreign key to a technicians table. There is no technician profile, capacity, skill set, or workload tracking.

3. **No concept of time.** The maintenance templates know `estimated_duration` in minutes, but there is no concept of:
   - Working calendar (working days, holidays, shifts)
   - Technician shift schedule
   - Production shutdown calendar

4. **Templates are category-coupled, not machine-coupled.** `maintenance_templates.machine_category` is a plain string (e.g., `"CNC"`, `"Compressor"`). There is no model-level specificity. A CNC-01 and CNC-99 with completely different configurations would get the same template.

5. **Checklist items are too simple.** Each checklist item has only `title`, `description`, `sequence`, and `is_required`. There is no:
   - Answer type (OK/Not OK, numeric measurement, text, photo required)
   - Expected measurement range (e.g., oil pressure: 2.5–3.5 bar)
   - Weight/severity for health score contribution

6. **No auto-scheduling.** Plans must be created manually one by one. There is no frequency engine (PM every 30 days, every 250 operating hours, etc.).

7. **No emergency/breakdown work order.** There is no `breakdown` module despite `operational_status = 'breakdown'` being a valid machine state.

8. **Planning board is a flat list.** The current planning index is a filterable list. There is no Calendar view, Timeline view, or Kanban board.

9. **The execution overall_score is a simplistic average.** `overallScore = totalScore / count`. This does not weight checklist items by severity or type.

10. **No rescheduling logic.** If a plan is moved or blocked, there is no cascading effect on other plans for the same machine, area, or technician.

11. **Performance risk in DashboardController.** The dashboard iterates over all plans and calls `getReadinessReport()` for each plan in a loop (N+1 query risk). The mock warehouse lookup is in a PHP array today, but this will be catastrophic against a real WMS with network latency.

---

### 1.5 Architecture Maturity Assessment

```
Foundation Layer (Machine Data)      ████████████████████░  90%
Integration Layer (Warehouse)        ████████████████░░░░░  75%
Template/SOP Layer                   ████████████░░░░░░░░░  55%
Planning Layer                       ████████░░░░░░░░░░░░░  40%
Execution Layer (Field)              █████████░░░░░░░░░░░░  45%
Analytics/Intelligence Layer         █░░░░░░░░░░░░░░░░░░░░   5%
UX / Planning Interface              ████░░░░░░░░░░░░░░░░░  20%
```

**Overall system maturity: ~58%**

---

## 2. Gap Analysis

### 2.1 What Exists

| Capability | Current State |
|---|---|
| Machine Registry | Full CRUD, QR, photos, documents |
| Sparepart-to-machine mapping | Done, with warehouse stock check |
| PM Template (SOP package) | Name, type, duration, simple checklist, sparepart list |
| PM Plan (1 plan = machine + template + date) | Manual creation only |
| PM Execution (field checklist) | QR scan, checklist, 1–5 score, 1 photo |
| Readiness Audit | 6-factor pre-flight check |
| Morning Briefing Dashboard | Today's agenda, blockers, priority list, timeline |
| Procurement Workflow | Full 7-stage approval chain |

### 2.2 What Is Missing (Gaps)

#### GAP 1 — No Real Technician Entity
- No `technicians` table with capacity, skills, certifications, area ownership
- No workload visibility
- Technician assigned via free-text string

#### GAP 2 — No Working Calendar
- No `working_calendars` table
- No holiday / shutdown / weekend logic
- Cannot answer "How many working days remain this month?"

#### GAP 3 — No PM Frequency / Recurrence Engine
- Templates have `maintenance_type` (Daily, Weekly, Monthly...) but there is no engine that reads this and auto-generates future plans
- Planner creates every plan manually

#### GAP 4 — No Breakdown / Emergency Work Order
- Machine has `operational_status = 'breakdown'` but no corresponding emergency WO module
- Cannot capture breakdown root cause, repair actions, or MTTR

#### GAP 5 — No Real Health Score Engine
- `getHealthScoreAttribute()` is hardcoded to specific machine codes
- Health is not calculated from inspection history, breakdown frequency, age, or PM compliance

#### GAP 6 — Checklist Items Lack Intelligence
- Items have no answer type (measurement, OK/NG, photo)
- No measurement range validation
- No per-item weight for health scoring
- Cannot capture "oil pressure was 2.1 bar" — only a 1–5 score

#### GAP 7 — No Dynamic Rescheduling
- Moving a plan has no cascading effect
- No conflict detection between technicians, same machine, or same area

#### GAP 8 — No Calendar / Timeline / Workload UX
- Current planning interface is a flat list with filters
- No calendar view (weekly/monthly)
- No Gantt/timeline
- No technician workload heatmap

#### GAP 9 — No Reporting & Analytics
- No PM compliance rate by area, machine, technician
- No MTBF / MTTR tracking
- No downtime analysis
- No trend reports for machine health

#### GAP 10 — Role System Not Activated
- Spatie permissions installed but roles not fully defined and applied to routes/controllers

---

## 3. Planning Engine Concept

### 3.1 The Core Philosophy

The planning engine must answer five questions every morning:

| Question | Data Needed |
|---|---|
| What must be done today? | Plans with `scheduled_date = today`, not completed |
| What is falling behind? | Plans with `scheduled_date < today`, not completed |
| What is at risk tomorrow? | Plans with `scheduled_date = tomorrow`, readiness check fails |
| Who should do each job? | Technician workload + area ownership |
| What is the machine's current risk? | Health score + last execution score + breakdown history |

---

### 3.2 Plan Generation — Two Modes

#### Mode A: Manual Planning (current, enhanced)
Planner manually selects: Machine → Template → Date → Technician → Priority

Enhanced with:
- Conflict warnings (machine already has a plan that day)
- Technician capacity warnings (technician overloaded)
- Sparepart pre-check before saving

#### Mode B: Automatic PM Generation (new)
The scheduler engine reads PM frequency from templates and generates future plans automatically.

**Frequency types to support:**

| Type | Description |
|---|---|
| Calendar-based | Every N days from last execution |
| Fixed calendar | Every 1st day of the month, every Monday |
| Operating hours | Every 250 machine hours (requires hour meter) |
| Event-based | After every breakdown |
| Hybrid | Every 30 days OR every 200 hours, whichever comes first |

**Generation logic:**

```
For each active machine:
  For each active template applicable to that machine:
    Find the last completed execution of that template on that machine
    Calculate next_due_date = last_execution_date + frequency_interval
    If next_due_date is within the planning horizon (e.g., next 30 days):
      Check if a plan already exists for this machine + template in that window
      If not, propose a new plan
      Assign to the area's default technician
      Run readiness pre-check
      Save as status = 'draft' or 'proposed'
```

**Planning horizon:** Configurable per company. Default: generate 4 weeks ahead.

---

### 3.3 Plan Status Lifecycle

```
proposed          ← Auto-generated, awaiting planner confirmation
     ↓
draft             ← Manually created or confirmed by planner
     ↓
waiting_approval  ← Submitted for supervisor review (for high-value PMs)
     ↓
approved          ← Ready to assign technician
     ↓
ready             ← Technician assigned, spareparts available
     ↓
in_progress       ← Technician scanned QR, execution started
     ↓
waiting_review    ← Execution submitted, awaiting supervisor sign-off
     ↓
completed         ← Signed off, machine health recalculated
     
[Any state] → postponed  ← Moved to a future date (with reason + log)
[Any state] → cancelled  ← Cancelled (with reason + log)
[Any state] → emergency  ← Override: machine breakdown, emergency WO opened
```

---

### 3.4 Priority Matrix

Every plan must have a calculated priority. Do NOT rely on the planner manually setting `low/medium/high/critical`.

**Priority Score formula (0–100):**

```
priority_score = 
  (machine_criticality_weight × 30)     // A=30, B=20, C=10
+ (machine_health_penalty × 25)         // (100 - health_score) / 4
+ (days_overdue_penalty × 20)           // min(days_overdue × 2, 20)
+ (pm_type_urgency × 15)               // Daily=15, Weekly=12, Monthly=8, Annual=3
+ (sparepart_risk_factor × 10)         // 10 if any required part is at reorder point
```

The planning board sorts by `priority_score` descending by default.

---

## 4. Machine Health Engine

### 4.1 Design Principles

Health is **not static**. It must be recalculated after every execution, every breakdown event, and on a nightly basis.

Health is a **weighted aggregate** of multiple dimensions:

| Dimension | Weight | Source |
|---|---|---|
| Last inspection score | 35% | `maintenance_executions.overall_score` (normalized to 0–100) |
| PM compliance rate | 25% | `completed_plans / total_plans` for last 90 days |
| Breakdown frequency | 20% | Breakdown count in last 90 days (inverse) |
| Machine age factor | 10% | Years since commissioning vs expected lifecycle |
| Outstanding critical defects | 10% | Unresolved items with score ≤ 1 in last 30 days |

### 4.2 Health Score Calculation Formula

```
inspection_component = (last_overall_score / 5.0) × 100 × 0.35

compliance_count = completed_plans_last_90d
total_count = all_plans_last_90d (excluding cancelled)
compliance_component = (compliance_count / max(total_count, 1)) × 100 × 0.25

breakdown_count = breakdowns_last_90d
breakdown_component = max(0, 100 - (breakdown_count × 20)) × 0.20

age_years = years_since_commissioning
expected_lifecycle = 20  // configurable per machine category
age_component = max(0, 100 - ((age_years / expected_lifecycle) × 100)) × 0.10

critical_defect_count = answers with score=1 in last 30d (unresolved)
defect_component = max(0, 100 - (critical_defect_count × 15)) × 0.10

health_score = round(
  inspection_component 
  + compliance_component 
  + breakdown_component 
  + age_component 
  + defect_component
)
```

### 4.3 Health Thresholds and Labels

| Score Range | Label | Color | Action |
|---|---|---|---|
| 85–100 | Healthy | Green | Normal PM schedule |
| 70–84 | Good | Light Green | Monitor closely |
| 55–69 | Fair | Yellow | Consider increasing PM frequency |
| 40–54 | Warning | Orange | Escalate to maintenance supervisor |
| 25–39 | Critical | Red | Emergency inspection required |
| 0–24 | Danger | Dark Red | Consider shutdown, assess for major overhaul |

### 4.4 Health Score Persistence

Health score must be **stored, not computed on the fly** for every page load.

```
machines table additions:
  health_score        DECIMAL(5,2)  -- stored, calculated nightly or on event
  health_last_updated TIMESTAMP
  health_trend        TINYINT       -- +1 improving, 0 stable, -1 declining
  health_detail_json  TEXT          -- JSON breakdown of each component score
```

**Recalculation triggers:**
1. After any PM execution is marked `completed`
2. After any breakdown event is opened or closed
3. Nightly batch job (Laravel Scheduler) at 01:00
4. Manual recalculation triggered by supervisor

---

## 5. Checklist Engine

### 5.1 The Problem with Current Checklists

The current `maintenance_template_checklists` table stores only:
- `title` (text)
- `description` (text)
- `sequence` (integer)
- `is_required` (boolean)

And the execution captures a `score` (1–5 integer) and `remarks` (text).

**This is insufficient for real maintenance inspection work.** A maintenance checklist must support:

| Checklist Item Type | Example |
|---|---|
| OK / Not OK (pass/fail) | "Is the safety guard intact?" |
| Numeric measurement with range | "Oil pressure: 2.5–3.5 bar" |
| Qualitative scale (1–5) | "Overall bearing noise: 1=Severe, 5=Normal" |
| Photo evidence required | "Photo of oil level indicator" |
| Free text | "Observations from previous maintenance" |
| Multi-select options | "Lubrication applied to: [A] [B] [C]" |

### 5.2 Proposed Checklist Item Schema

```
maintenance_template_checklists:
  id
  maintenance_template_id   FK → maintenance_templates
  sequence                  INT
  title                     VARCHAR
  description               TEXT (nullable)
  section                   VARCHAR (nullable)  -- group items: "Lubrication", "Electrical", etc.
  answer_type               ENUM:
                              'ok_ng'           -- OK / Not OK toggle
                              'score_1_5'       -- 1–5 scale (current behavior)
                              'measurement'     -- numeric with unit and range
                              'text'            -- free text entry
                              'photo'           -- camera capture required
                              'checklist'       -- multiple sub-items checkbox
  unit                      VARCHAR (nullable)  -- e.g., "bar", "°C", "mm", "rpm"
  min_value                 DECIMAL (nullable)  -- lower acceptable bound
  max_value                 DECIMAL (nullable)  -- upper acceptable bound
  is_required               BOOLEAN
  requires_photo_on_fail    BOOLEAN             -- force photo if result is NG or out of range
  health_weight             DECIMAL(4,2)        -- 0.00–1.00, contribution to health score
  severity_on_fail          ENUM: 'low', 'medium', 'high', 'critical'
  sort_order                INT
```

### 5.3 Machine-Type-Specific Checklists

Templates should be linkable to specific machine categories AND to specific machine models:

```
Link priority:
  1. Template explicitly assigned to Machine ID (most specific)
  2. Template assigned to Machine Model
  3. Template assigned to Machine Category (current behavior — least specific)
```

This allows a Compressor AAP-1000 and a Compressor Atlas Copco GA-55 to have different checklists even though they are both in the "Compressor" category.

### 5.4 Template-Machine Assignment Table

```
maintenance_template_machine_links (new table):
  id
  maintenance_template_id  FK → maintenance_templates
  link_type                ENUM: 'machine', 'machine_model', 'machine_category'
  link_value               VARCHAR  -- machine.id, machine.model, or machine.category
  is_primary               BOOLEAN  -- default template for this machine
  created_at / updated_at
```

### 5.5 Checklist Sections

Group checklist items into sections to reflect real maintenance work steps:

**Example: Hydraulic Press PM Checklist**

```
Section 1: Pre-Start Safety Check
  ☐ Safety fence and guards intact? [OK/NG, critical]
  ☐ Emergency stop functional? [OK/NG, critical]
  ☐ No visible oil leaks before startup? [OK/NG, high]

Section 2: Hydraulic System
  ☐ Hydraulic oil level [measurement, 60–80%, medium]
  ☐ Hydraulic pressure at idle [measurement, 180–220 bar, high]
  ☐ Hydraulic oil temperature [measurement, 35–55°C, medium]
  ☐ Seal condition — any visible seepage? [OK/NG + photo, high]

Section 3: Cylinder & Actuator
  ☐ Cylinder rod surface condition [score 1–5, medium]
  ☐ Alignment check [score 1–5, medium]

Section 4: Electrical
  ☐ Control panel indicators [OK/NG, low]
  ☐ Motor temperature [measurement, <70°C, medium]

Section 5: Lubrication
  ☐ Grease points lubricated [checklist, medium]
  ☐ Lubrication applied: [A] Guide rail [B] Platen [C] Pivot points
```

### 5.6 Execution Answer Storage

```
maintenance_execution_answers (enhanced):
  id
  execution_id           FK → maintenance_executions
  checklist_item_id      FK → maintenance_template_checklists
  answer_ok_ng           TINYINT(1) nullable  -- 1=OK, 0=NG
  answer_score           TINYINT nullable     -- 1–5 scale
  answer_numeric         DECIMAL(10,3) nullable  -- measurement value
  answer_text            TEXT nullable
  answer_options         JSON nullable        -- selected items from multi-select
  is_out_of_range        BOOLEAN             -- auto-calculated: numeric outside min/max
  is_failed              BOOLEAN             -- auto-calculated: NG or out_of_range
  remarks                TEXT nullable
  requires_followup      BOOLEAN             -- flagged for follow-up action
  followup_priority      ENUM: 'low','medium','high','critical' nullable
  photo_required         BOOLEAN
```

---

## 6. Dynamic Scheduling Engine

### 6.1 The Scheduling Problem

Maintenance scheduling is a **constrained resource allocation problem**. Every day, the planner must fit:
- N plans (work to be done)
- Into M working days
- Using K technicians with capacity C_k
- Respecting area ownership
- Avoiding machine downtime conflicts
- Considering PM frequency requirements

### 6.2 Scheduling Constraints

**Hard Constraints (must not be violated):**
1. Technician cannot be in two places at the same time
2. Machine can only have one PM executing at a time
3. PM cannot be scheduled on a non-working day (unless explicitly allowed)
4. PM cannot be scheduled while machine is in `breakdown` or `maintenance` status

**Soft Constraints (should be respected, warn if violated):**
1. Same technician should not be scheduled for > 8 hours/day
2. Area PIC should be assigned to machines in their area
3. Critical-A machines should not have PM skipped more than 7 days from due date
4. PM requiring 2 technicians should have both technicians available

### 6.3 Rescheduling Triggers

| Trigger | Action |
|---|---|
| Plan postponed by planner | System suggests next available slot for same machine + technician |
| Emergency breakdown opened | Flag all upcoming PMs for that machine as "Review Required" |
| Technician marked absent | Identify all plans for that technician that day, propose reassignment |
| Sparepart remains out of stock | Flag plans requiring that sparepart, suggest rescheduling |
| Machine moved to maintenance status | Block all non-emergency plans for that machine |

### 6.4 Rescheduling Algorithm (Conceptual)

```
Procedure: RescheduleChain(plan, reason, new_date)

1. Validate: new_date must be a working day
2. Log: create plan_reschedule_log entry {plan_id, old_date, new_date, reason, triggered_by}
3. Find affected plans:
   same_machine_plans = plans for same machine within [old_date, new_date]
   same_technician_plans = plans for same technician on new_date
   same_area_plans = plans in same area on new_date
4. For each affected plan:
   Check technician capacity on new_date
   If technician over capacity: warn planner, suggest alternative technician
   If machine conflict: warn planner, block execution
5. Update plan.scheduled_date = new_date
6. Update plan.status = 'postponed' with reason
7. Return: affected_plans list with warnings for planner review
```

### 6.5 Emergency Work Order Flow

When a machine breaks down:

```
Machine transitions to operational_status = 'breakdown'
      ↓
System auto-creates a Breakdown Work Order (BWO)
      ↓
BWO assigned to on-call technician (configurable per area)
      ↓
All pending PMs for that machine are flagged 'at-risk'
      ↓
Planner reviews: postpone pending PMs or keep schedule
      ↓
BWO completed: root cause captured, repair actions logged
      ↓
Machine transitions to 'running' or 'maintenance'
      ↓
Health score recalculated (takes breakdown penalty)
      ↓
Post-breakdown PM automatically proposed (within 3–5 days)
```

---

## 7. Technician Workload Engine

### 7.1 Technician Master Data

Currently, technicians are stored as a free-text string in `maintenance_plans.assigned_technician`. This must be elevated to a proper entity.

```
technicians (new table):
  id
  employee_id       VARCHAR UNIQUE    -- company employee ID
  name              VARCHAR
  email             VARCHAR nullable
  phone             VARCHAR nullable
  area_id           FK → master_production_areas nullable  -- primary area
  skill_tags        JSON              -- ['hydraulics','electrical','pneumatics']
  max_hours_per_day DECIMAL(4,1)      -- default: 8.0
  is_active         BOOLEAN
  user_id           FK → users nullable  -- link to login account
```

### 7.2 Technician Availability

```
technician_availability (new table):
  id
  technician_id     FK → technicians
  date              DATE
  status            ENUM: 'available', 'leave', 'sick', 'training', 'offsite'
  notes             TEXT nullable
  created_by        FK → users
```

### 7.3 Workload Calculation

For any given date or date range, the system must be able to answer:

```
For technician T on date D:
  scheduled_minutes = SUM(template.estimated_duration) 
                      WHERE plan.assigned_technician = T.id
                        AND plan.scheduled_date = D
                        AND plan.status NOT IN ('completed', 'cancelled')
  
  capacity_minutes = T.max_hours_per_day × 60
  
  utilization_pct = (scheduled_minutes / capacity_minutes) × 100
  
  available_minutes = capacity_minutes - scheduled_minutes
  
  is_overloaded = utilization_pct > 90
```

### 7.4 Workload Visualization

The technician workload must be visible as a **heatmap matrix**:

```
         | Mon 28 | Tue 29 | Wed 30 | Thu 31 | Fri 01 |
---------|--------|--------|--------|--------|--------|
Budi     |  85%   |  40%   | 100%▲  |  60%   |  20%   |
Fadil    |  30%   |  90%   |  45%   |  75%   |  80%   |
Setiawan |  60%   |  55%   |  70%   |  30%   | 100%▲  |
Hidayat  |  45%   |  20%   |  35%   |  85%   |  55%   |

▲ = overloaded (>90%)
Color: Green (0–60%) → Yellow (61–80%) → Orange (81–90%) → Red (>90%)
```

### 7.5 Automatic Workload Balancing Suggestion

When auto-generating plans, the system must:

1. Calculate each technician's utilization for the proposed date
2. Find the technician in the correct area with the lowest utilization
3. Assign that technician as the default (planner can override)
4. If no technician in the area is available (all >90%), warn the planner

This is **suggestion-based**, not force-assignment. The human planner always has final authority.

---

## 8. Calendar UX Design

### 8.1 View Modes and When to Use Each

The planning interface must offer **5 view modes**, each serving a different planning need:

---

#### View 1: Agenda (Default — Morning Briefing)

**When:** Every morning before the shift starts.

**Shows:** Today's tasks grouped by area, sorted by priority. Each card shows machine code, PM type, technician, estimated duration, readiness status badge.

**Inspired by:** Google Calendar's Agenda view, Notion's daily view.

```
TODAY — Monday, July 28, 2026                    [3 Ready] [2 Blocked] [1 In Progress]

─── FOUNDRY AREA ────────────────────────────────────────────────
  ● [READY]   ARM-12 — Monthly PM           Fadil      2h    08:00
  ● [BLOCKED] CNC-08 — Weekly Lubrication   Budi       30m   08:30   ⚠ Parts missing

─── MACHINING AREA ──────────────────────────────────────────────
  ● [READY]   DRL-19 — Daily Inspection     Setiawan   15m   07:30
  ✓ [DONE]    PMP-08 — Monthly PM           Hidayat    3h    Completed 09:45
```

---

#### View 2: Week Calendar

**When:** Weekly planning review (typically Monday morning).

**Shows:** 7-day column grid. Each column is a day. Plans shown as cards in the column. Technician color-coding. Drag-and-drop rescheduling.

**Inspired by:** Google Calendar weekly view, ClickUp Calendar.

```
         Mon 28   Tue 29   Wed 30   Thu 31   Fri 01   Sat 02   Sun 03
08:00  | ARM-12  |         | CNC-04 |         |         |        |
       | Monthly |         | Quart  |         |         |        |
09:00  |         | PMP-08  |         | DRL-22  |         |        |
       |         | Monthly |         | Weekly  |         |        |
```

---

#### View 3: Month Calendar

**When:** Monthly planning and PM compliance overview.

**Shows:** Full calendar month grid. Plans shown as dots or compact chips. Color represents status (green=completed, yellow=scheduled, red=overdue). Click a day to expand.

**Inspired by:** Asana, Notion Calendar, eMaint.

---

#### View 4: Timeline / Gantt

**When:** Capacity planning, project-level maintenance planning, annual PM planning.

**Shows:** Horizontal timeline. Left axis = technicians or machines. Horizontal bars = plan duration. 

```
MACHINES VIEW — July 2026

CNC-08    ████ ████       ████                    ████
CNC-04         ████   ████   ████                
ARM-12    ██████████                    ████████████
DRL-19    ████████████████████████████████████████

          |--Week 1--|--Week 2--|--Week 3--|--Week 4--|

Legend: ██ = Completed  ░░ = Scheduled  ▓▓ = Overdue  ▒▒ = In Progress
```

---

#### View 5: Kanban Board

**When:** Real-time status tracking during the day.

**Shows:** Columns = plan status. Cards = each plan. Drag card to change status.

```
[TO DO]         [IN PROGRESS]    [WAITING REVIEW]   [DONE]
ARM-12 Monthly  CNC-08 Weekly    PMP-08 Monthly     DRL-19 Daily
DRL-22 Weekly                   
BRG-07 Quart
```

---

### 8.2 UX Interaction Standards

- **Drag and drop:** Week and Kanban views support drag-and-drop rescheduling. On drop, system shows conflict warnings before confirming.
- **Quick-edit popover:** Click any plan card → popover shows key details + quick actions (Assign Technician, Postpone, View Readiness, Print WO).
- **Inline filters:** Filter by Area, Criticality, Technician, Status, Readiness in all views simultaneously.
- **Persistent view memory:** System remembers the user's last selected view and filter combination.
- **Real-time status:** Plans in progress show live elapsed time. Plans past due pulse red.

---

## 9. Recommended Database Model

> **Important:** This section is **conceptual only**. No migrations. No code. Only the logical data model to guide future implementation.

### 9.1 Enhanced Existing Tables

```
machines (add columns):
  health_score          DECIMAL(5,2)  DEFAULT 100
  health_last_updated   TIMESTAMP
  health_trend          TINYINT       -- +1, 0, -1
  health_detail_json    TEXT          -- JSON breakdown
  operating_hours       INT DEFAULT 0 -- for hours-based PM
  last_pm_date          DATE nullable

maintenance_plans (add columns):
  technician_id         FK → technicians (nullable, replace string)
  estimated_start_time  TIME nullable   -- when during the day
  actual_start_time     TIME nullable
  postponed_from        DATE nullable   -- original scheduled_date if moved
  postpone_count        INT DEFAULT 0
  postpone_reason       TEXT nullable
  emergency_work_order  BOOLEAN DEFAULT false
  parent_plan_id        FK → maintenance_plans nullable -- for split/linked plans
  priority_score        INT nullable  -- calculated priority 0–100
  generation_notes      TEXT nullable -- auto-generated notes from scheduler

maintenance_template_checklists (add columns):
  section               VARCHAR(100) nullable
  answer_type           ENUM(...) as described in Section 5.2
  unit                  VARCHAR(20) nullable
  min_value             DECIMAL(10,3) nullable
  max_value             DECIMAL(10,3) nullable
  requires_photo_on_fail BOOLEAN DEFAULT false
  health_weight         DECIMAL(4,2) DEFAULT 1.00
  severity_on_fail      ENUM('low','medium','high','critical') DEFAULT 'medium'

maintenance_executions (add columns):
  technician_id         FK → technicians nullable
  actual_duration_mins  INT nullable
  machine_hours_at_execution INT nullable  -- hour meter reading
  supervisor_id         FK → users nullable
  reviewed_at           TIMESTAMP nullable
  review_notes          TEXT nullable

maintenance_template_spareparts (add columns):
  consumption_type      ENUM('replace','top_up','inspect_only')
  estimated_qty         DECIMAL(8,2)
```

### 9.2 New Tables Required

```
technicians
  id, employee_id, name, email, phone, area_id(FK),
  skill_tags(JSON), max_hours_per_day(DECIMAL), is_active, user_id(FK)

technician_availability
  id, technician_id(FK), date, status(ENUM), notes, created_by(FK)

working_calendars
  id, name, year, is_default, created_by(FK)

working_calendar_entries
  id, calendar_id(FK), date, type(ENUM: working/holiday/company_holiday/shutdown)
  description, is_full_day

pm_frequencies
  id, maintenance_template_id(FK), machine_id(FK nullable), 
  machine_category(VARCHAR nullable), frequency_type(ENUM: days/hours/event),
  interval_value(INT),  -- e.g., 30 (days), 250 (hours)
  tolerance_days(INT),  -- allowed variance window
  auto_generate(BOOLEAN), look_ahead_days(INT DEFAULT 30),
  last_generated_at(TIMESTAMP)

maintenance_template_machine_links
  id, maintenance_template_id(FK), link_type(ENUM: machine/model/category),
  link_value(VARCHAR), is_primary(BOOLEAN)

breakdown_work_orders
  id, machine_id(FK), reported_by(VARCHAR), reported_at(TIMESTAMP),
  symptom_description(TEXT), failure_mode(VARCHAR nullable),
  failure_component(VARCHAR nullable), repair_actions(TEXT nullable),
  root_cause(TEXT nullable), technician_id(FK nullable),
  started_at(TIMESTAMP nullable), completed_at(TIMESTAMP nullable),
  mttr_minutes(INT nullable),  -- calculated
  linked_plan_id(FK → maintenance_plans nullable),
  status(ENUM: open/in_progress/resolved/closed),
  verified_by(FK → users nullable), verified_at(TIMESTAMP nullable)

machine_health_logs
  id, machine_id(FK), health_score(DECIMAL), health_trend(TINYINT),
  component_scores(JSON), calculated_at(TIMESTAMP),
  trigger_event(ENUM: execution/breakdown/nightly/manual),
  trigger_id(INT nullable)  -- references the execution or breakdown that triggered

plan_reschedule_logs
  id, plan_id(FK), rescheduled_by(FK → users), old_date(DATE), new_date(DATE),
  reason(TEXT), trigger_type(ENUM: manual/auto/breakdown/absence/parts_shortage)

technician_plan_assignments
  id, plan_id(FK), technician_id(FK), role(ENUM: primary/secondary),
  assigned_by(FK → users), assigned_at(TIMESTAMP)
  -- Supports plans that require multiple technicians

maintenance_execution_followups
  id, execution_id(FK), checklist_item_id(FK), description(TEXT),
  priority(ENUM), assigned_to(FK → users nullable),
  due_date(DATE nullable), resolved_at(TIMESTAMP nullable),
  resolved_by(FK → users nullable), resolution_notes(TEXT nullable)
```

### 9.3 Entity Relationship Summary

```
Machine ─────────────── has many ─── MaintenancePlans
   │                                      │
   ├── has many ─── MachineComponents      ├── belongs to ─── MaintenanceTemplate
   ├── has many ─── MachinePhotos          │                       │
   ├── has many ─── MachineDocuments       │                   has many ─── Checklists
   ├── has many ─── RequiredSpareparts     │                   has many ─── TemplateSpareparts
   ├── belongs to ── ProductionArea        │
   ├── has many ─── HealthLogs             ├── belongs to ─── Technician
   └── has many ─── BreakdownWorkOrders    │                       │
                                           │                  belongs to ─── ProductionArea
                                           └── has one ─── MaintenanceExecution
                                                                   │
                                                           has many ─── Answers
                                                           has many ─── Photos
                                                           has many ─── Followups
```

---

## 10. Roadmap

### Phase 1 — Foundation Hardening (Estimated: 3–4 weeks)
*Priority: Stabilize what exists before adding new features*

| Task | Description |
|---|---|
| 1.1 Technician Entity | Create `technicians` table, migrate string → FK in plans |
| 1.2 Checklist Enhancement | Add `answer_type`, `unit`, `min_value`, `max_value`, `health_weight` to checklist items |
| 1.3 Enhanced Execution | Capture numeric answers, out-of-range flags, per-item photos |
| 1.4 Real Health Score | Implement `HealthScoreService` replacing the hardcoded attribute |
| 1.5 Role Activation | Define and apply roles: Admin Maintenance, Kabag, Technician, Supervisor, Viewer |
| 1.6 Performance Fix | Cache readiness report, fix N+1 queries in Dashboard |

**Deliverable:** System runs correctly with real data, no more mocks, roles enforced.

---

### Phase 2 — Planning Intelligence (Estimated: 4–5 weeks)
*Priority: Move from manual plan creation to semi-automated scheduling*

| Task | Description |
|---|---|
| 2.1 Working Calendar | Create `working_calendars` with holiday/shutdown entries |
| 2.2 PM Frequency Engine | Create `pm_frequencies` table, build `PmSchedulerService` |
| 2.3 Auto-Generation | Cron job generates proposed plans for next 30 days |
| 2.4 Priority Score | Implement `PriorityCalculationService`, auto-calculate priority_score |
| 2.5 Rescheduling Engine | Build `ReschedulingService` with conflict detection and cascade logic |
| 2.6 Breakdown WO Module | Create Breakdown WO form, link to machine status, MTTR capture |
| 2.7 Technician Availability | Absence/leave management for technicians |

**Deliverable:** Planner can review auto-proposed plans, approve, and see conflict warnings.

---

### Phase 3 — UX Transformation (Estimated: 4–6 weeks)
*Priority: Transform the flat list into a professional planning interface*

| Task | Description |
|---|---|
| 3.1 Agenda View | Enhanced today view with area grouping, priority sorting, readiness badges |
| 3.2 Week Calendar | Drag-and-drop week view with conflict detection |
| 3.3 Month Calendar | PM compliance overview, color-coded completion |
| 3.4 Workload Heatmap | Technician × Day utilization matrix |
| 3.5 Gantt Timeline | Machine or Technician timeline for long-horizon planning |
| 3.6 Kanban Board | Status-column board for real-time daily tracking |
| 3.7 Mobile Optimization | Field technician mobile interface improvements |
| 3.8 Print Templates | Enhanced Work Order print with QR, sparepart list, previous notes |

**Deliverable:** Planning interface is visually on par with ClickUp / Asana, operable on tablet by field supervisors.

---

### Phase 4 — Analytics & Intelligence (Estimated: 4–5 weeks)
*Priority: Convert operational data into strategic insights*

| Task | Description |
|---|---|
| 4.1 PM Compliance Report | Compliance rate by area, machine, technician, month |
| 4.2 MTBF/MTTR Report | Mean Time Between Failures, Mean Time To Repair tracking |
| 4.3 Health Trend Dashboard | Machine health history, improving/declining trends |
| 4.4 Sparepart Consumption | How often each part is used in PMs, consumption forecast |
| 4.5 Technician Performance | PM completion rate, average quality score per technician |
| 4.6 Downtime Analysis | Downtime by machine, area, failure mode |
| 4.7 Critical Defect Tracker | Open defects from checklists, follow-up status, aging |
| 4.8 Executive Dashboard | KPI summary for management: availability, compliance, cost |

**Deliverable:** System produces monthly reports that replace manual Excel tracking.

---

### Phase 5 — Integration & Advanced Features (Estimated: 5–6 weeks)
*Priority: Connect to the wider ecosystem*

| Task | Description |
|---|---|
| 5.1 Real WMS Connection | Replace MockWarehouseRepository with live WMS database query |
| 5.2 ERP/SAP Integration | (if applicable) bidirectional sync with ERP for cost tracking |
| 5.3 Hour Meter Integration | Accept machine operating hours via API or manual input |
| 5.4 IoT Sensor Hook | Optional: accept temperature/vibration readings to trigger alerts |
| 5.5 Notification Engine | Email/SMS/in-app notifications for overdue PMs, health alerts |
| 5.6 Mobile App | Native or PWA mobile app for technicians |
| 5.7 API Layer | REST API for external systems to query machine status, plans |

**Deliverable:** System is a hub, not an island. Fully integrated with the plant's operational ecosystem.

---

## 11. Risk Analysis

### 11.1 Operational Risks

| Risk | Probability | Impact | Mitigation |
|---|---|---|---|
| Technicians refuse to use the mobile checklist | Medium | High | Involve technicians in UX design. Keep mobile interface extremely simple. Provide training. Show how it benefits them (no paperwork). |
| Supervisors continue to use Excel for planning | High | High | The system must be faster and easier than Excel from Day 1. Calendar drag-and-drop is essential. Import from Excel feature for transition period. |
| Plans are created but never executed | Medium | High | Morning dashboard must visibly show overdue plans. Alert notifications to supervisors daily. |
| Machine data quality is poor | High | Medium | Progressive completion already implemented. Add a data quality score per machine on the passport page. |

### 11.2 Technical Risks

| Risk | Probability | Impact | Mitigation |
|---|---|---|---|
| N+1 query performance degradation | High | High | Readiness check is already expensive (O(n) DB + warehouse calls). Cache readiness results for 5 minutes. Pre-calculate health scores nightly. |
| Health score formula is too aggressive | Medium | Medium | Start conservative. Let the maintenance team tune the weights after 1 month of live data. Store weights in a configurable settings table. |
| Auto-scheduling creates nonsensical plans | Medium | High | Auto-generation creates plans in `proposed` status, not `approved`. Planner must review before plans become active. Never auto-confirm. |
| Real WMS has high latency | Medium | High | Warehouse data should be pulled on a schedule (e.g., every 30 minutes) and cached locally. Do not call WMS in real-time on every page load. |
| SQLite is used in development | Medium | Low | Fine for development. Switch to MySQL/PostgreSQL before production. Ensure no SQLite-specific SQL is used. |

### 11.3 User Adoption Risks

| Risk | Probability | Impact | Mitigation |
|---|---|---|---|
| System is too complex for maintenance staff | Medium | High | Phase 1–2 should focus on simplicity. Only expose complexity in Phase 3+ after users are comfortable. |
| Planner does not trust auto-generated plans | High | Medium | System never executes automatically. All auto-generated plans are proposals that require human approval. |
| Mobile interface doesn't work in factory conditions | Medium | High | Test in actual conditions (low light, oily hands, gloves, noise). Large touch targets. High contrast mode. Offline capability for checklist completion. |
| Management does not see value | Low | High | Phase 4 analytics must produce a compelling executive dashboard. Translate uptime and compliance data into cost savings. |

### 11.4 Performance Risk Details

The current `DashboardController` has a critical performance issue:

```php
// Current code — O(n × m) complexity where n=plans, m=spareparts per plan
$allPlans->each(function ($p) {
    $p->readiness = $this->readinessService->getReadinessReport($p); // ← N+1 + warehouse call
});
```

With 200 plans and 8 spareparts each = **1,600 warehouse lookups per dashboard load**.

**Solution:** Persist calculated readiness status and refresh on events, not on every request.

---

## 12. Final Recommendation

### As CTO of this company, here is how I would evolve this system:

---

### 12.1 The Highest-Priority Decision: Shift the Mental Model

The biggest risk is that this system remains a "machine database with a planning feature."

The product must be **repositioned internally as a Planning Command Center** — the single source of truth for all maintenance decisions. 

Every meeting in the maintenance department should start by opening this system's Dashboard. Every technician should begin their day by looking at the Agenda view. Every supervisor should review the week's workload on Monday morning using the Calendar view.

This is a cultural change, not a technical one. And the system's UX must drive this culture.

---

### 12.2 Build the Health Engine First

If I had to choose one single feature to build next, it would be the **real Machine Health Engine**.

Because health score is the central signal that drives everything else:
- Priority of PM plans
- Frequency adjustments
- Escalation alerts
- Management reporting

Without a real health score, the system is data collection. With a real health score, it becomes intelligence.

---

### 12.3 The Checklist is the Most Important Field Interface

The quality of the health score depends entirely on the quality of the checklist data.

**Right now, technicians input a 1–5 score.** That is almost useless for real analysis.

**The system needs:** "Oil pressure measured at 2.1 bar" (below minimum 2.5 bar) → auto-flags as out-of-range → adds to health penalty → triggers follow-up work order.

This level of specificity is what separates a CMMS from a task tracker.

---

### 12.4 Never Auto-Execute. Always Auto-Propose.

The biggest mistake in PM automation is removing human judgment.

**The correct architecture:**
```
Algorithm proposes → Human planner reviews → Human approves → System executes
```

The auto-scheduling engine should always create plans in `proposed` status. A human must always click "Approve" before a plan becomes real. This prevents bad data from creating real-world problems.

---

### 12.5 Design for the Tablet in the Hand of the Supervisor

The primary user of the planning interface is not a developer. It is a **Maintenance Supervisor or Planner sitting in a noisy plant with a tablet**.

Design priorities:
1. **Speed:** Any screen must load in < 2 seconds
2. **Clarity:** Status must be readable at a glance (large color-coded badges)
3. **Touch-first:** All interactions must work with a finger, not just a mouse
4. **Offline resilience:** Checklist execution must work without internet (sync when back online)

---

### 12.6 The Long-Term Vision: Predictive Maintenance

Once the system has 12–18 months of real inspection data, health history, and breakdown records, it gains the ability to do something powerful:

**Predict failures before they happen.**

Not AI magic — just pattern recognition:
- "Last 3 times CNC-08's bearing noise score dropped below 3, a breakdown followed within 14 days."
- "Every August, PMP-08's hydraulic temperature spikes due to ambient heat. Pre-emptively schedule a cooling system check in late July."

This is achievable with the data model described in this document, standard Laravel scheduled jobs, and simple statistical analysis. No machine learning required at this stage.

---

### 12.7 Summary of Priorities by Investment ROI

| Priority | Feature | ROI |
|---|---|---|
| 1 | Real Health Score Engine | Highest — drives all intelligence |
| 2 | Enhanced Checklists (measurements) | Highest — quality input = quality output |
| 3 | Technician Entity + Workload View | High — eliminates the #1 planner complaint |
| 4 | Auto PM Generation + Calendar View | High — saves 2–3 hours/week of manual planning |
| 5 | Breakdown WO Module | High — closes the gap in the breakdown lifecycle |
| 6 | Working Calendar | Medium — required for accurate scheduling |
| 7 | Analytics & Compliance Reports | Medium — justifies the system to management |
| 8 | Real WMS Connection | Medium-High — depends on company's WMS status |
| 9 | Mobile App / PWA | Medium — current mobile web is 70% good enough |
| 10 | ERP / IoT Integration | Low (long term) — enterprise feature for Phase 5+ |

---

### 12.8 Final Words

This system has **excellent bones.** The Machine Passport, Sparepart mapping, Readiness Audit, and Procurement workflow are sophisticated and well-designed. The team behind this system thinks in the right direction.

What the system needs is not more CRUD features.

It needs:
1. **Intelligence** — Real health scores that mean something
2. **Automation** — Auto-generating plans that the human approves, not creates from scratch
3. **Visualization** — A Calendar/Timeline that makes the planner feel in control
4. **Feedback loops** — Every execution feeding back into health scores and future planning

If these four capabilities are implemented, this system will be a genuine CMMS capable of handling a medium-sized manufacturing plant with 100–500 machines — comparable in functional scope (if not in polish) to Fiix or UpKeep.

The difference between this system and SAP PM is not sophistication. It is that SAP PM has 30 years and 10,000 consultants behind it. This system has the advantage of being **built for one specific plant, by people who understand that plant.** That focus, if maintained, is a competitive strength.

**Build for the plant. Build for the planner. Build for the technician in the field.**

Everything else follows.

---

*End of Document*

---
> This document is a living architecture proposal. It should be reviewed and updated after each phase of implementation to reflect lessons learned and changing requirements.
