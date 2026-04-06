# Project Management Module: In-Depth Architecture & Data Flow (Exhaustive Technical Spec)

This document provides a highly detailed, developer-level mapping of the KNR-Hrms Project Management module. It goes beyond high-level architecture to map exact database structures, service method contracts, view integrations, and the precise sequence of operations across the system lifecycle.

---

## 1. Complete Database Mapping (Models & Schema)

The module revolves around the following database schema, structured relationally. Below are the key tables and their exact fillable/mapped columns.

### 1.1 `Project` (`projects` table)
The root container linking billing, visibility, and high-level schedules.
*   **Foreign Keys**: `tenant_id`, `client_id`
*   **Identity**: `name`, `code`, `description`
*   **State & Access**: 
    *   `status` (Enum: planning, active, etc.)
    *   `visibility` (Enum: public, team_locked, stealth, freelancer_mode)
*   **Schedule & Config**: `start_date`, `deadline`, `gamification_settings` (JSON)
*   **Financials**: `billing_type`, `hourly_rate`, `currency`
*   **Relationships**: `modules()`, `stages()`, `sprints()`, `priorities()`, `tasks()`, `taskTemplates()`, `assignments()`, `repositories()`.

### 1.2 `Task` (`project_tasks` table)
The atomic unit of work, carrying execution mapping and DevOps pointers.
*   **Foreign Keys**: `project_id`, `module_id`, `sprint_id`, `stage_id`, `blocked_by_task_id`, `created_by` (User)
*   **Content**: `title`, `description`, `status`, `priority`, `complexity`
*   **Tracking**: `start_date`, `due_date`, `estimated_hours`, `actual_hours`, `scrum_points`
*   **Financials**: `billable` / `is_billable` (Boolean), `invoice_id`, `billed_at`
*   **DevOps Bindings**: `git_branch_url`, `git_pr_url`, `qa_notes`, `deployed_to`
*   **System**: `version` (optimistic locking)
*   **Relationships**: `sprint()`, `assignees()` (Polymorphic/Employee), `comments()`, `checklists()`, `activities()`, `timesheets()`, `bugTicket()`.

### 1.3 `Sprint` (`sprints` table)
Time-boxed execution cycles.
*   **Foreign Keys**: `project_id`
*   **Core**: `name`, `goal`, `status`
*   **Schedule**: `start_date`, `end_date`
*   **Relationships**: `tasks()`, `logs()` (`SprintLog` for iterative changes).

### 1.4 `BugTicket` (`bug_tickets` table)
Dedicated defect tracking integrated with automated SLA monitors.
*   **Foreign Keys**: `project_id`, `module_id`, `task_id`, `workflow_stage_id`
*   **Polymorphic Ties**: 
    *   Reporter: `reporter_id`, `reporter_type` (Can be user or client)
    *   Assignee: `assignee_id`, `assignee_type`
*   **Defect Details**: `subject`, `description`, `severity`, `priority`, `steps_to_reproduce`, `resolution_summary`
*   **System & Meta**: `environment_metadata` (JSON), `attachments` (Array), `is_client_visible`, `custom_view_tags`
*   **SLA & Time**: `hours_spent`, `started_at`, `resolved_at`, `sla_due_at`, `is_sla_breached`
*   **Feedback**: `rating`, `rating_feedback`
*   **Relationships**: `stage()`, `forensics()`, `transitions()`, `pendingApproval()`.

### 1.5 `Timesheet` (Time Execution Log)
Tracks literal hours spent mapping employee -> task -> project.
*   **Foreign Keys**: `employee_id`, `project_id`, `task_id`
*   **Details**: `date`, `task_description`, `task_type`
*   **Metrics**: `hours_spent`, `is_billable`
*   **State & Compliance**: `status`, `violation_flags` (e.g. over-allocation).

---

## 2. Service & Controller Contracts (Logic Mapping)

KNR-Hrms employs "Thin Controllers, Fat Services". The controllers merely authorize and redirect; Services hold the complex math and logic.

### 2.1 Backend Controllers 
*   `ProjectController@store`: Instantiates basic project shell.
*   `PlannerApiController@index`: Calculates Gantt/Matrix overlaps and serves heavily nested JSON mapping resources to timelines.
*   `TaskController@update`: Handles the PUT request for Kanban movement. Crucially implements HTTP 303 Redirects for Inertia compliance.
*   `DevOpsDashboardController@commits`: Hydrates the DevOps view with pulled `git_commits`.
*   `BugTrackerController@transition`: Moves bugs through lifecycle stages.

### 2.2 Core Business Services (`app/Services/ProjectManagement/`)
*   **`AssignmentLogic.php`**: Evaluates employee capacity before task assignment. Analyzes scheduled `tasks` mapped across all active `projects` to compute a user's `ResourceMatrix` overlap percentage.
*   **`DependencyService.php`**: Validates whether moving a `Task` violates constraints set by `blocked_by_task_id`.
*   **`TaskActivityService.php`**: Hook triggered on Eloquent `saved` events (or via Observers) to automatically append delta changes to the `task_activities` audit table.
*   **`TaskAutomationService.php`**: Listens to state events (e.g. PR merged) and automatically routes `project_tasks.status`.
*   **`GitAutomationService.php`**: Receives GitLab/GitHub webhooks, resolving commit hashes to `Task.git_branch_url` and dropping records in `git_commits` table.
*   **`ScrumMetricService.php`**: Runs differential logic comparing `sprints.start_date` bounds versus `tasks.actual_hours` and `tasks.scrum_points` to generate velocity arrays for charts.
*   **`DocumentSync.php`**: Creates standardized structures (MOMs, DFDs) inside `ProjectDocument` upon stage completion.

---

## 3. UI to Controller Mapping (Vue Pages)

The platform utilizes Inertia.js heavily parsing data from the controllers directly into Vue props.

*   `Pages/Project/Board.vue`: The Kanban layout. Passes `tasks` and iterates through reactive columns. Triggers `@update` pushing payloads to `TaskController@update`.
*   `Pages/Project/Planner/Index.vue`: The Visual Planner container. Has sub views (`GanttChart`, `ResourceMatrix`). Relies fully on raw JSON provided by `PlannerApiController`.
*   `Pages/Project/DevOps/Dashboard.vue`: Tracks CI/CD. Fetches paginated `commitsData` dynamically via `axios.get(route('projects.devops.commits', projectId))` mapping to `DevOpsDashboardController`.
*   `Pages/Project/BugTracker/Hub.vue`: Client and Dev unified bug view mapping over `bug_tickets`.

---

## 4. In-Depth Lifecycle Data Flow

### Step 1: Inception -> Resourcing (The Matrix)
1.  **Creation:** Payload sent to `ProjectController@store`. Generates a `Project` (with `visibility` rules).
2.  **Structuring:** The manager uses the Visual Planner (`Planner.vue`). The `PlannerApiController` hydrates the frontend with `ProjectStage` and existing cross-project schedules.
3.  **Allocation:** `AssignmentLogic.php` determines who is free. Employees are hard-linked to the project via `WorkAssignment` polymorphic relations.

### Step 2: Granular Execution (Sprints & Tasks)
1.  **Iteration Bind:** `SprintController@store` creates a time horizon (`sprints`).
2.  **Task Instantiation:** `TaskController@store` generates `project_tasks`, mapping to `sprint_id`, setting `estimated_hours` and locking optimistic `version` to 0. 
3.  **Binding:** The task is mapped to the employee via the `task_assignees` pivot table.

### Step 3: Action & Audit (Kanban to Database)
1.  **Movement:** User drags a task column in `Board.vue`. Vue makes a `PUT /tasks/{id}` request.
2.  **Constraint Check:** `DependencyService.php` blocks if `blocked_by_task_id` is unresolved.
3.  **Update:** Controller updates `status`.
4.  **Audit Generation:** `TaskActivityService.php` intercepts the state change and drops a line in `task_activities` ("User X moved Task Y from Todo -> In Progress at Time Z").

### Step 4: DevOps Synchronization (Automated Updating)
1.  **Code Push:** Developer pushes to a branch.
2.  **Webhook Delivery:** Git provider POSTs to the server.
3.  **Resolution:** `GitAutomationService.php` reads the payload. If the branch name matches a rule (e.g. `feature/task-44`), it pulls `Task::find(44)`.
4.  **Meta Injection:** The `git_branch_url` is updated on the `Task`. If it's a Pull Request merge event, `TaskAutomationService.php` auto-transitions the task to "QA Review" stage.

### Step 5: Issue Remediation (Bug Tracking)
1.  **Client Reporting:** End-user hits the client portal, triggering `TicketPortalController`.
2.  **Defect Creation:** Drops a row in `bug_tickets`, parsing JSON to `environment_metadata`. Starts the SLA clock (`started_at`).
3.  **Forensics:** The `BugTrackerController` alerts the team through `WarRoom.vue`.
4.  **SLA Monitor:** If `resolved_at` > `sla_due_at`, background jobs flag `is_sla_breached = true`.

### Step 6: Finalization & Sign-off (Approvals)
1.  **Stage Gating:** Task moves to "Done". `WorkflowService.php` triggers.
2.  **Approval Engine:** Checks if the `project_stages` requires sign-off. If so, writes to `approvals` and `approval_steps`.
3.  **Timesheet Summation:** Upon close, all `timesheets` linked via `task_id` are summed against `estimated_hours`.
4.  **Documentation:** `DocumentSync.php` may auto-compile the sprint logs (`SprintLog`) into a MOM `ProjectDocument` locked into the system.
